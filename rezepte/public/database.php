<?php
    define("INSTALLATION_PATH", "/var/www/Rezepte-Server/");

    abstract class Severity{
        const Info = "Info";
        const Warning = "Warning";
        const Unknown = "Unknown";
        const Critical = "Critical";
    }

    function logInPage($msg, $severity = Severity::Info){
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

            echo($sql);

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
    }
?>