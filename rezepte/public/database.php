<?php
    define("INSTALLATION_PATH", "/var/www/Rezepte-Server/");

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
            "connection_recipe_ingredients",
            "connection_recipe_allergenes"
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
                SELECT "name","id" FROM "$table";
            SQL;
            $results = $this->query($sql);
            while ($row = $results->fetchArray()) {
                array_push($answer["data"], $row);
            }
        }

        // Execute a DML on a simple table
        public function stInsert($table, $value){
            if(strlen($value) <= 0){
                return;
            }
            $sql = <<<SQL
                insert into "$table" ("name") values ("$value");
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
    }

    $db = new Database();

    // Useful for debug purposes
    //logInPage(var_export($_POST, true));    
    if(isset($_POST["insert"])){
        // sanitize
        $table = htmlspecialchars($_POST["insert"]);
        if(isset($_POST["name"])){
            $value = htmlspecialchars($_POST["name"]);
            switch($table){
                case "allergenes":
                    $db->stInsert($table, $value);
                    break;
                case "units":
                    $db->stInsert($table, $value);
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
                default:
                    logInPage("Skipping this table with no handler: \"".$table."\"");
                    break;
            }
        }else{
            logInPage("Missing \$_POST variable \"id\"", Severity::Critical);
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
?>