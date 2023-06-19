<?php
    require("config.php");
    require("upload.php");
    require("recipe.php");

    abstract class Severity{
        const Info = "Info";
        const Warning = "Warning";
        const Unknown = "Unknown";
        const Critical = "Critical";
    }

    $answer = array(
        "status" => Severity::Info,
        "statusId" => 0,
        "logs" => [],
        "data" => []
    );

    function logInPage($msg, $severity = Severity::Info){
        # use global var here
        global $answer;

        $cssclass = "text-";
        switch($severity){
            case Severity::Info:
                $cssclass .= "success";
                break;
            case Severity::Warning:
                $cssclass .= "warning";
                break;
            case Severity::Unknown:
                $cssclass .= "secondary";
                break;
            case Severity::Critical:
                $cssclass .= "danger";
                break;
        }

        array_push($answer["logs"],array(
            "severity" => $severity,
            "msg" => $msg
        ));

        if(!isset($_GET["mode"])){
            return;
        }

        if($_GET["mode"] != "html"){
            return;
        }

        # Print Logs if enabled by GET var
        echo <<<HTML
            <p>
                <b class="$cssclass">$severity:</b> $msg</code>
            </p>
        HTML;
    }

    class Database{
        const FILE = "assets/rezepte.sqlite3";
        const DDL_FILE = INSTALLATION_PATH."ddl.sql";
        const IMG_DIR = "assets/images";

        #list of required tables
        const REQUIRED_TABLES = [
            "ingredients",
            "recipes",
            "units",
            "allergenes",
            "cri",
            "cra",
            "dishtypes"
        ];

        # Members:
        public $db;

        public function __construct(){
            # Ensure a database exists. If not create one
            if(!file_exists(INSTALLATION_PATH.self::FILE)){
                $newdb = INSTALLATION_PATH.self::FILE;
                logInPage("New Database will be created at <code>".$newdb."</code>");
                touch($newdb);
            }
            try{
                $this->db = new SQLite3(INSTALLATION_PATH.self::FILE);
            }catch(Exception $e){
                logInPage('Caught exception: '.  $e->getMessage(). ". Please review File Permissions of <code>".INSTALLATION_PATH.self::FILE."</code>", Severity::Critical);
            }

            $check = self::verifyDB();
            if($check < 0){
                logInPage("Found ".-$check." less tables than expected. Running DDL statements again!");
                self::init();
            }

            // Enable verification of Foreign keys.
            $this->exec("PRAGMA foreign_keys = ON;");
            $this->exec('PRAGMA journal_mode = wal;');
        }

        # Checks and verifies all data and runs DDL statements if necesary
        # returns difference: found-expected
        function verifyDB(){
            $tablecount = count(self::REQUIRED_TABLES);
            $matchtables = "";
            for($i = 0; $i < $tablecount; $i++){
                $matchtables .= 'name = "'.self::REQUIRED_TABLES[$i].'"';
                if($i < $tablecount - 1){
                    $matchtables .= " OR\n";
                }
            }

            $sql = <<<SQL
                SELECT 
                    count(name)
                FROM 
                    sqlite_schema
                WHERE 
                    type ='table' AND
                    $matchtables AND
                    name NOT LIKE 'sqlite_%';
            SQL;

            //echo($sql);

            $matchcount = $this->db->querySingle($sql);
            return $matchcount - $tablecount;
        }
        # Runs all DDL statements form the sql file
        # performs checks afterwards.
        # return 0 on success
        function init(){
            if(!file_exists(self::DDL_FILE)){
                logInPage("Can't find DDL-statements file ".self::DDL_FILE."! Aborting.", Severity::Critical);
                return -1;
            }
            # get ddl statements
            $ddl = file_get_contents(self::DDL_FILE);
            # execute DDL
            if(!$this->db->exec($ddl)){
                logInPage("Failed to execute DDL statements. Please review file: ".self::DDL_FILE);
                return -2;
            }

            if(self::verifyDB() < 0){
                logInPage(
                    "DDL Statements were successfully run. However, there are missing tables in the SQL. Please review file: <code>".self::DDL_FILE."</code> and compare against <code>database.php</code> variable <code>Database::REQUIRED_TABLES</code>",
                    Severity::Warning
                );
                return -1;
            }

            return 0;
        }
        # Execute a select statement
        public function query($sql){
            return $this->db->query($sql);
        }

        public function exec($sql){
            if(!$this->db->exec($sql)){
                logInPage("Failed to execute this query: <code>".$sql."</code><br>".$this->db->lastErrorMsg(), Severity::Critical);
                return -1;
            }
            return 0;
        }
        /*
            @brief Queries a simple table witha a name and id columns. Writes selected columns in rows
            @param $table name of the table
        */
        public function stQuery($table){
            # use global var here
            global $answer;
            
            $sql = <<<SQL
                SELECT "name","id" FROM "$table" order by id asc;
            SQL;
            $results = $this->query($sql);
            while ($row = $results->fetchArray()) {
                array_push($answer["data"], $row);
            }
        }
        // Execute a DML on a simple table
        public function stInsert($table, $value, $uniqueign = false){
            if(strlen($value) <= 0){
                return;
            }

            $ign = "";
            if($uniqueign){
                $ign = "or ignore";
            }

            $sql = <<<SQL
                insert $ign into "$table" ("name") values ("$value");
            SQL;
            $this->exec($sql);
        }

        public function stDelete($table, $id){
            if($id < 0){
                return;
            }
            $sql = <<<SQL
                delete from "$table" where "id" = "$id";
            SQL;
            $this->exec($sql);
        }

        public function stSearch($table, $name, $searchcol = "name"){
            $sql = <<<SQL
                SELECT "id" FROM "$table" WHERE "$searchcol" = "$name";
            SQL;
            $ret =  $this->db->querySingle($sql);
            if($ret){
                return $ret;
            }else{
                return -1;
            }
        }

        // Makes HTML Special chars only if necessary. Is being used as markdown
        function sanitize($string){
            if(
                strstr(strtoupper($string), "DROP") && strstr($string, ";") ||
                strstr(strtoupper($string), "DELETE") && strstr($string, ";") ||
                strstr(strtoupper($string), "<SCRIPT>")
            ){
                logInPage("Injection attempt!", Severity::Warning);
                $warning = "**Dieses Rezept war ein versuch eine Datanbank-Injektion!**\n\n";
                return $warning.htmlspecialchars($string);
            }

            return $string;
        }

        public function recipeInsert(){
            if(!isset($_POST["rezeptname"]) || !isset($_POST["zubereitung"])){
                logInPage('"rezeptname" oder "zubereitung" nicht gesetzt!');
                return -1;
            }

            //$ret = recipe_create(ASSET_DIR."Borsch.json");
            $recipe = recipe_create();
            logInPage(var_export($recipe, true));
            /*
                Standard Variables
            */
            $title = $this->sanitize($recipe["title"]);  

            //Validate
            if(isset($_POST["insert"])){
                $rid = $this->stSearch("recipes", $title, "title");
                if($rid > 0){
                    logInPage("Rezept \"".$title."\" gibt es schon! Abbruch.",Severity::Warning);
                    return -1;
                }
            }

            $description = $this->sanitize($recipe["description"]);
            // Dishtype
            $dishtype = 1;
            if(strlen($recipe["dishtype_search"]) > 0){
                $dishtype = $this->stSearch("dishtypes", $recipe["dishtype_search"]);
            }else{
                $dishtype = $recipe["dishtype"];
            }
            
            $time = $recipe["time"];
            /*
                Image
            */
            $image = "NULL";
            if(strlen($recipe["url"]) > 0){
                // URL is set
                $this->stInsert("images", $recipe["url"], true);
                $imgid = $this->stSearch("images", $recipe["url"]);
                //logInPage("ID is ".$imgid." for ".$recipe["url"]);
                if($imgid > 0){
                    $image = $imgid;
                }
            }
            // full URL. e.g https:// or /bla/bla
            $furl = str_contains($recipe["url"], "/");
            //logInPage(var_export($_FILES, true));
            // Only upload a file if it is a local URL and it exists
            if(!$furl && isset($_FILES[RECIPE_PF_IMG])){
                $filename = basename( $_FILES[RECIPE_PF_IMG]["name"]);
                // upload
                $err = uploadFile(RECIPE_PF_IMG, ASSET_DIR."images/");
                if($err != UploadError::OK){
                    logInPage("Fehler beim Hochladen von ".htmlspecialchars($filename)." - ".$err, Severity::Warning);
                }
            }
            /*
                Build Query & run
            */
            $sql = <<<SQL
                INSERT INTO "recipes"
                ("title", "text", "dishtype", "time", "image") VALUES
                ("$title", "$description", "$dishtype", $time, $image);
            SQL;
            $this->exec($sql);
            $rid = $this->stSearch("recipes", $title, "title");
            if($rid < 0){
                logInPage("Fehler beim hinzufügen von ".$title, Severity::Critical);
                return -1;
            }

            //Clear connecction table before adding
            $sql = <<<SQL
                DELETE FROM "cri" WHERE "recipe" = $rid;
            SQL;
            $this->exec($sql);
            // Iterate through all Ingredients
            $length = count($recipe["ingredients"]);
            for($i = 0; $i < $length; $i++){
                $zutat = $recipe["ingredients"][$i];
                /*
                    Neue zudaten mit vorhandenen vergleichen und ggf. hinzufügen
                */
                $zutat_id = $this->stSearch("ingredients", $zutat["name"]);
                if($zutat_id < 0){
                    $this->stInsert("ingredients", $zutat["name"]);
                    $zutat_id = $this->stSearch("ingredients", $zutat["name"]);                    
                }

                $menge = $zutat["amount"];
                // Search if non-numeric
                $einheit_id = 1;
                if(is_numeric($zutat["unit"])){
                    $einheit_id = $zutat["unit"];
                }else{
                    $einheit_id = $this->stSearch("units", $zutat["unit"]);         
                }

                $sql = <<< SQL
                    INSERT INTO "cri"
                    ("recipe", "ingredient", "amount", "unit") VALUES
                    ($rid, $zutat_id, $menge, $einheit_id);
                SQL;
                $this->exec($sql);
                //logInPage($_POST["zutat"][$i].", ".$_POST["menge"][$i].", ".$_POST["einheit"][$i]);
                //logInPage("ID of ".$_POST["zutat"][$i]." is: ".$this->stSearch("ingredients", $_POST["zutat"][$i]));
            }
            /*
                Allergenes
            */
            
            //Clear connection table before adding
            $sql = <<<SQL
                DELETE FROM "cra" WHERE "recipe" = $rid;
            SQL;
            $this->exec($sql);            

            // Search if search defined
            if(count($recipe["allergenes_search"]) > 0){
                array_push(
                    $recipe["allergenes"],
                    $this->stSearch("allergenes", $recipe["allergenes_search"][0])
                );
            }
            $allergen_count = count($recipe["allergenes"]);
            // Iterate through all Allegenes
            for($a = 0; $a < $allergen_count; $a++){
                $aid = $recipe["allergenes"][$a];
                if($aid < 1){
                    continue;
                }
                // Add to table
                $sql = <<<SQL
                    INSERT INTO "cra"
                    ("recipe", "allergene") VALUES
                    ($rid, $aid);
                SQL;
                //logInPage($sql);
                $this->exec($sql);
            }
            return 0;
        }
    }

    $db = new Database();

    // Useful for debug purposes
    //logInPage(var_export($_POST, true));
    //logInPage(var_export($_FILES, true)); 
    if(isset($_POST["insert"])){
        // sanitize
        $table = htmlspecialchars($_POST["insert"]);
        if($_POST["insert"] == "recipes" || isset($_POST["rezeptname"])){
            $answer["statusId"] = $db->recipeInsert();
        }else if(isset($_POST["name"])){
            $value = htmlspecialchars($_POST["name"]);
            switch($table){
                case "allergenes":
                    $db->stInsert($table, $value);
                    break;
                case "units":
                    $db->stInsert($table, $value);
                    break;
                case "dishtypes":
                    $db->stDelete($table, $value);
                    break;  
                default:
                    logInPage("Skipping this table with no handler: \"".$table."\"");
                    break;
            }
        }else{
            logInPage("Missing \$_POST variable \"name\"", Severity::Critical);
        } 
    }else if(isset($_POST["delete"])){
        // sanitize
        $table = htmlspecialchars($_POST["delete"]);
        if(isset($_POST["id"])){
            $value = intval($_POST["id"]);
            switch($table){
                case "allergenes":
                    $db->stDelete($table, $value);
                    break;
                case "units":
                    $db->stDelete($table, $value);
                    break;
                case "dishtypes":
                    $db->stDelete($table, $value);
                    break; 
                default:
                    logInPage("Skipping this table with no handler: \"".$table."\"");
                    break;
            }
        }else{
            logInPage("Missing \$_POST variable \"id\"", Severity::Critical);
        }        
    }else if(isset($_FILES["snapshot"])){
        $err = uploadFile("snapshot", SNAPSHOT_DIR);
        if($err != UploadError::OK){
            logInPage($err);
        }else{
            $src = SNAPSHOT_DIR.$_FILES["snapshot"]["name"];
            $dest = ASSET_DIR."rezepte.sqlite3";
            rename($src, $dest);
        }
    }

    if(isset($_GET["select"])){
        // sanitize
        $table = htmlspecialchars($_GET["select"]);
        
        switch($table){
            case "allergenes":
                $db->stQuery($table);
                break;
            case "units":
                $db->stQuery($table);
                break;
            case "dishtypes":
                $db->stQuery($table);
                break;  
            default:
                logInPage("Skipping this table with no handler: \"".$table."\"");
                break;
        }
    }
    /*
        FINALLY, Send all as JSON Data
    */
    if(!isset($_GET["mode"])){
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($answer);
    }
    $db->db->close();
?>