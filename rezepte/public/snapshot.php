<?php
    require("config.php");
    $filenamebase = "rezepte-".date("Y-m-d").".tar.gz";
    $filename = SNAPSHOT_DIR.$filenamebase;
    
    // Verify Snapshot dir
    if(!file_exists(SNAPSHOT_DIR)){
        exec("mkdir ".SNAPSHOT_DIR);
    }

    chdir(INSTALLATION_PATH);
    $command = "tar -czvf $filename assets/";
    $ex = exec($command);
    if(!$ex){
        die("Unable to execute this command: ".$command);   
    }

    $fp = fopen($filename, "r");
    if(!$fp){
        die("Unable to open file ".$filename);
    }
    $content = fread($fp, filesize($filename));
    fclose($fp);

    //send as tar.gz
    header("Content-type: application/gzip;");
    // specify download name
    header('Content-Disposition: attachment; filename="'.$filenamebase.'"');
    echo($content);
    //Delete file afterwards
    unlink($filename);
?>