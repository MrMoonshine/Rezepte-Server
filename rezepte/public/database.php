<?php
require("config.php");
require("upload.php");
require("recipe.php");

abstract class Severity
{
    const Info = "Info";
    const Warning = "Warning";
    const Unknown = "Unknown";
    const Critical = "Critical";
}

$answer = array(
    "status" => Severity::Info,
    "statusId" => 0,
    "logs" => [],
    "data" => [],
    "rowcount" => 0 // used for complex queries, to count on behalf of partial filters
);

function logInPage($msg, $severity = Severity::Info)
{
    # use global var here
    global $answer;

    $cssclass = "text-";
    switch ($severity) {
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

    array_push($answer["logs"], array(
        "severity" => $severity,
        "msg" => $msg
    )
    );

    if (!isset($_GET["mode"])) {
        return;
    }

    if ($_GET["mode"] != "html") {
        return;
    }

    # Print Logs if enabled by GET var
    echo <<<HTML
            <p>
                <b class="$cssclass">$severity:</b> $msg</code>
            </p>
        HTML;
}

function matchStrArr($str, $arr)
{
    $matches = 0;
    for ($i = 0; $i < count($arr); $i++) {
        if (strtolower($str) == strtolower($arr[$i])) {
            $matches++;
        }
    }
    return $matches > 0;
}

class Database
{
    const FILE = "assets/rezepte.sqlite3";
    const DDL_FILE = INSTALLATION_PATH . "ddl.sql";
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

    public function __construct()
    {
        # Ensure a database exists. If not create one
        if (!file_exists(INSTALLATION_PATH . self::FILE)) {
            $newdb = INSTALLATION_PATH . self::FILE;
            logInPage("New Database will be created at <code>" . $newdb . "</code>");
            touch($newdb);
        }
        try {
            $this->db = new SQLite3(INSTALLATION_PATH . self::FILE);
        } catch (Exception $e) {
            logInPage('Caught exception: ' . $e->getMessage() . ". Please review File Permissions of <code>" . INSTALLATION_PATH . self::FILE . "</code>", Severity::Critical);
        }

        $check = self::verifyDB();
        if ($check < 0) {
            logInPage("Found " . -$check . " less tables than expected. Running DDL statements again!");
            self::init();
        }

        // Enable verification of Foreign keys.
        $this->exec("PRAGMA foreign_keys = ON;");
        $this->exec('PRAGMA journal_mode = wal;');
    }

    # Checks and verifies all data and runs DDL statements if necesary
    # returns difference: found-expected
    function verifyDB()
    {
        $tablecount = count(self::REQUIRED_TABLES);
        $matchtables = "";
        for ($i = 0; $i < $tablecount; $i++) {
            $matchtables .= 'name = "' . self::REQUIRED_TABLES[$i] . '"';
            if ($i < $tablecount - 1) {
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
    function init()
    {
        if (!file_exists(self::DDL_FILE)) {
            logInPage("Can't find DDL-statements file " . self::DDL_FILE . "! Aborting.", Severity::Critical);
            return -1;
        }
        # get ddl statements
        $ddl = file_get_contents(self::DDL_FILE);
        # execute DDL
        if (!$this->db->exec($ddl)) {
            logInPage("Failed to execute DDL statements. Please review file: " . self::DDL_FILE);
            return -2;
        }

        if (self::verifyDB() < 0) {
            logInPage(
                "DDL Statements were successfully run. However, there are missing tables in the SQL. Please review file: <code>" . self::DDL_FILE . "</code> and compare against <code>database.php</code> variable <code>Database::REQUIRED_TABLES</code>",
                Severity::Warning
            );
            return -1;
        }

        return 0;
    }
    # Execute a select statement
    public function query($sql)
    {
        return $this->db->query($sql);
    }

    public function exec($sql)
    {
        if (!$this->db->exec($sql)) {
            logInPage("Failed to execute this query: <code>" . $sql . "</code><br>" . $this->db->lastErrorMsg(), Severity::Critical);
            return -1;
        }
        return 0;
    }
    /*
        @brief Queries a simple table witha a name and id columns. Writes selected columns in rows
        @param $table name of the table
    */
    public function stQuery($table)
    {
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
    public function stInsert($table, $value, $uniqueign = false)
    {
        if (strlen($value) <= 0) {
            return;
        }

        $ign = "";
        if ($uniqueign) {
            $ign = "or ignore";
        }

        $sql = <<<SQL
                insert $ign into "$table" ("name") values ("$value");
            SQL;
        $this->exec($sql);
    }

    public function stDelete($table, $id)
    {
        if ($id < 0) {
            return;
        }
        $sql = <<<SQL
                delete from "$table" where "id" = "$id";
            SQL;
        $this->exec($sql);
    }

    public function stSearch($table, $name, $searchcol = "name")
    {
        $sql = <<<SQL
                SELECT "id" FROM "$table" WHERE "$searchcol" = "$name";
            SQL;
        $ret = $this->db->querySingle($sql);
        if ($ret) {
            return $ret;
        } else {
            return -1;
        }
    }

    // Makes HTML Special chars only if necessary. Is being used as markdown
    function sanitize($string)
    {
        if (
            strstr(strtoupper($string), "DROP") && strstr($string, ";") ||
            strstr(strtoupper($string), "DELETE") && strstr($string, ";") ||
            strstr(strtoupper($string), "<SCRIPT>")
        ) {
            logInPage("Injection attempt!", Severity::Warning);
            $warning = "**Dieses Rezept war ein versuch eine Datanbank-Injektion!**\n\n";
            return $warning . htmlspecialchars($string);
        }

        return $string;
    }

    public function recipeInsert($json_file = "")
    {
        //$ret = recipe_create(ASSET_DIR."Borsch.json");
        $recipe = recipe_create($json_file);
        //logInPage(var_export($recipe, true));
        /*
            Standard Variables
        */
        $title = $this->sanitize($recipe["title"]);
        //Validate
        if (isset($_POST["insert"])) {
            $rid = $this->stSearch("recipes", $title, "title");
            if ($rid > 0) {
                logInPage("Rezept \"" . $title . "\" gibt es schon! Abbruch.", Severity::Warning);
                return -1;
            }
        }

        $description = $this->sanitize($recipe["description"]);
        $amount = $this->sanitize($recipe["amount"]);
        // Verify
        if (strlen($title) < 1 || strlen($description) < 1) {
            logInPage('"rezeptname" oder "zubereitung" nicht gesetzt!');
            return -1;
        }
        // Dishtype
        $dishtype = 1;
        if (strlen($recipe["dishtype_search"]) > 0) {
            $dishtype = $this->stSearch("dishtypes", $recipe["dishtype_search"]);
        } else {
            $dishtype = $recipe["dishtype"];
        }

        $time = $recipe["time"];
        /*
            Image
        */
        $image = "NULL";
        if (strlen($recipe["url"]) > 0) {
            // URL is set
            $this->stInsert("images", $recipe["url"], true);
            $imgid = $this->stSearch("images", $recipe["url"]);
            //logInPage("ID is ".$imgid." for ".$recipe["url"]);
            if ($imgid > 0) {
                $image = $imgid;
            }
        }
        // full URL. e.g https:// or /bla/bla
        $furl = str_contains($recipe["url"], "/");
        //logInPage(var_export($_FILES, true));
        // Only upload a file if it is a local URL and it exists
        if (!$furl && isset($_FILES[RECIPE_PF_IMG])) {
            $filename = basename($_FILES[RECIPE_PF_IMG]["name"]);
            // upload
            $err = uploadFile(RECIPE_PF_IMG, ASSET_DIR . "images/");
            if ($err != UploadError::OK) {
                logInPage("Fehler beim Hochladen von " . htmlspecialchars($filename) . " - " . $err, Severity::Warning);
            }
        }
        /*
            Build Query & run
        */
        $sql = <<<SQL
                INSERT INTO "recipes"
                ("title", "description", "dishtype", "time", "image", "amount") VALUES
                ("$title", "$description", "$dishtype", $time, $image, $amount);
            SQL;
        $this->exec($sql);
        $rid = $this->stSearch("recipes", $title, "title");
        if ($rid < 0) {
            logInPage("Fehler beim hinzufügen von " . $title, Severity::Critical);
            return -1;
        }

        //Clear connecction table before adding
        $sql = <<<SQL
                DELETE FROM "cri" WHERE "recipe" = $rid;
            SQL;
        $this->exec($sql);
        // Iterate through all Ingredients
        $length = count($recipe["ingredients"]);
        //logInPage(var_export($recipe["ingredients"], true));
        for ($i = 0; $i < $length; $i++) {
            $zutat = $recipe["ingredients"][$i];
            $name = $zutat["name"];
            $menge = $zutat["amount"];
            $einheit = $zutat["unit"];

            /*
                Neue zudaten mit vorhandenen vergleichen und ggf. hinzufügen
            */
            $zutat_id = $this->stSearch("ingredients", $name);
            if ($zutat_id < 0) {
                $this->stInsert("ingredients", $name);
                $zutat_id = $this->stSearch("ingredients", $name);
            }


            // Search if non-numeric
            $einheit_id = 1;
            if (is_numeric($einheit)) {
                $einheit_id = $einheit;
            } else {
                $einheit_id = $this->stSearch("units", $einheit);
            }
            // To prevent foreign key errors
            $einheit_id = abs($einheit_id);

            $sql = <<<SQL
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
        if (count($recipe["allergenes_search"]) > 0) {
            array_push(
                $recipe["allergenes"],
                $this->stSearch("allergenes", $recipe["allergenes_search"][0])
            );
        }
        $allergen_count = count($recipe["allergenes"]);
        // Iterate through all Allegenes
        for ($a = 0; $a < $allergen_count; $a++) {
            $aid = $recipe["allergenes"][$a];
            if ($aid < 1) {
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

    public function recipeImportJson()
    {
        $files = scandir(ASSET_DIR);
        //logInPage(var_export($files, true));

        for ($i = 0; $i < count($files); $i++) {
            $filename = ASSET_DIR . $files[$i];
            if (mime_content_type($filename) == "application/json") {
                $ret = $this->recipeInsert($filename);
                if ($ret >= 0) {
                    // Delete files afterwards.
                    //unlink($filename);
                }
            }
        }
    }
    // Relies on GET variables
    public function recipeQuery()
    {
        global $answer;
        // Set order
        $order = "title";

        $sql = "select count(1) from recipes;";
        $rowcount = $this->db->querySingle($sql);
        if($rowcount){
            $answer["rowcount"] = $rowcount;
        }
        // set this get var to only get the row counter
        if (isset($_GET["count"])) {
            return 0;
        }

        if(isset($_GET["id"])){
            // Query just a single recipe
            $id = intval($_GET["id"]);
            $sql = <<<SQL
                select 
                    rownum,
                    id,
                    title,
                    amount,
                    time,
                    image,
                    dishtype,
                    dtid,
                    description
                from v_recipe_main
                where id = $id;
            SQL;

            $results = $this->query($sql);
            while ($row = $results->fetchArray()) {
                array_push($answer["data"], $row);
            }
            // if select wa ssuccessful
            if(count($answer["data"]) < 1){
                logInPage("Rzeept mit ID ".$id." gibt es nich!", Severity::Critical);
                return -1;
            }
            /*
                Query ingredients
            */
            $sql = <<<SQL
                select * from v_recipe_ingredients where recipe = $id;
            SQL;
            $results = $this->query($sql);
            $answer["data"][0]["ingredients"] = array();
            while ($row = $results->fetchArray()) {
                array_push($answer["data"][0]["ingredients"], $row);
            }
            return 0;
        }
        /*
            Filter for Pages
        */
        $pagefilter = "1 = 1";
        if(isset($_GET["items"]) && isset($_GET["page"])) {
            $items = intval($_GET["items"]);
            $page = intval($_GET["page"]);
            $limlow = $items * $page;
            $highlim = $limlow + $items;
            $pagefilter = "rownum > ".$limlow." and rownum <= ".$highlim;
        }
        /*
            Filter for Recipe Name
        */
        $titlefilter = "1 = 1";
        if(isset($_GET["title"])){
            $titlef = htmlspecialchars($_GET["title"]);
            $titlefilter = 'v_recipe_card.title like "%'.$titlef.'%"';
        }
        /*
            Filter for dishtype
        */
        $dishtypefilter = "1 = 1";
        if(isset($_GET["dishtype"])){
            $dishtypef = intval($_GET["dishtype"]);
            $dishtypefilter = 'v_recipe_card.dtid = '.$dishtypef;
        }
        /*
            Ingredient Filter
        */
        $ingredientfilter = "1 = 1";
        if(isset($_GET["ingredients"])){
            $len = count($_GET["ingredients"]);
            if($len > 0){
                $ingredientfilter = "";
            }
            for($a = 0; $a < $len; $a++){
                if($a != 0){
                    $ingredientfilter .= " and ";
                }
                $iname = htmlspecialchars($_GET["ingredients"][$a]);
                $ingredientfilter .= <<<SQL
                    EXISTS(
                        select * from v_recipe_ingredients
                        where v_recipe_ingredients.recipe = v_recipe_card.id
                        and v_recipe_ingredients.name like "%$iname%"
                    )
                SQL;
            }
        }
        /*
            Allergene Filter
        */
        $allergenefilter = "1 = 1";
        if(isset($_GET["allergenes"])){
            $len = count($_GET["allergenes"]);
            if($len > 0){
                $allergenefilter = "";
            }
            for($a = 0; $a < $len; $a++){
                if($a != 0){
                    $allergenefilter .= " and ";
                }
                $aid = intval($_GET["allergenes"][$a]);
                $allergenefilter .= <<<SQL
                    EXISTS(
                        select * from cra where cra.recipe = v_recipe_card.id and cra.allergene = $aid
                    )
                SQL;
            }
        }
        /*
            Filter for estimated time
        */
        $timefilter = "1 = 1";
        if(isset($_GET["time"])){
            $time = intval($_GET["time"]);
            $timefilter = "v_recipe_card.time <= ".$time;
        }
        // select ROW_NUMBER () OVER ( ORDER BY title ) RowNum,title from v_recipe_main;
        /*
            Query for Card view
        */
        $sql = <<<SQL
            select 
                ROW_NUMBER() OVER (ORDER BY $order) rownum,
                id,
                title,
                time,
                image,
                dishtype,
                allergenes,
                description
            from v_recipe_card
            where $titlefilter
            and $dishtypefilter
            and $ingredientfilter
            and $allergenefilter
            and $timefilter
            group by v_recipe_card.id
        SQL;

        //logInPage($sql);

        $pagesql = <<<SQL
            with pagedcte as(
                $sql
            )select * from pagedcte
            where $pagefilter;
        SQL;

        $results = $this->query($pagesql);
        if(!$results){
            logInPage("Failed to execute this query: ".$pagesql . " Error was: ".$this->db->lastErrorMsg(), Severity::Critical);
            return -1;
        }
        while ($row = $results->fetchArray()) {
            array_push($answer["data"], $row);
        }
        // Count, using the same filter. It is necessary to do it as cte because of the group by statement
        $pagesql = <<<SQL
            with countcte as(
                $sql
            )select count(1) from countcte;
        SQL;
        $answer["rowcount"] = $this->db->querySingle($pagesql);
    }
}

$db = new Database();
$simple_tables = ["allergenes", "units", "dishtypes"];

// Useful for debug purposes
//logInPage(var_export($_POST, true));
//logInPage(var_export($_GET, true));
//logInPage(var_export($_FILES, true)); 
if (isset($_POST["insert"])) {
    // sanitize
    $table = htmlspecialchars($_POST["insert"]);
    if ($_POST["insert"] == "recipes" || isset($_POST["rezeptname"])) {
        $answer["statusId"] = $db->recipeInsert();
    } else if (isset($_POST["name"])) {
        $value = htmlspecialchars($_POST["name"]);
        switch ($table) {
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
                logInPage("Skipping this table with no handler: \"" . $table . "\"");
                break;
        }
    } else {
        logInPage("Missing \$_POST variable \"name\"", Severity::Critical);
    }
} else if (isset($_POST["delete"])) {
    // sanitize
    $table = htmlspecialchars($_POST["delete"]);
    if (isset($_POST["id"])) {
        $value = intval($_POST["id"]);
        switch ($table) {
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
                logInPage("Skipping this table with no handler: \"" . $table . "\"");
                break;
        }
    } else {
        logInPage("Missing \$_POST variable \"id\"", Severity::Critical);
    }
} else if (isset($_FILES["snapshot"])) {
    $err = uploadFile("snapshot", SNAPSHOT_DIR);
    if ($err != UploadError::OK) {
        logInPage($err);
    } else {
        $src = SNAPSHOT_DIR . $_FILES["snapshot"]["name"];
        $dest = ASSET_DIR . "rezepte.sqlite3";
        rename($src, $dest);
    }
}

if (isset($_GET["select"])) {
    // sanitize
    $table = htmlspecialchars($_GET["select"]);
    if (matchStrArr($table, $simple_tables) > 0) {
        $db->stQuery($table);
    } else if ($table == "recipes") {
        $db->recipeQuery();
    } else {
        logInPage("Skipping this table with no handler: \"" . $table . "\"");
    }
} else if (isset($_GET["import"])) {
    if ($_GET["import"] == "json") {
        $db->recipeImportJson();
    }
}
/*
    FINALLY, Send all as JSON Data
*/
if (!isset($_GET["mode"])) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($answer);
}
$db->db->close();
?>