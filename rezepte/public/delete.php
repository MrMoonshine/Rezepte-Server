<?php
    $filepath = "/var/www/Rezepte-Server/assets/";
    if(isset($_GET['filename'])){
        $deletepath = $_GET['filename'];
        unlink($filepath.$deletepath);
        echo("Das rezept wurde gelöscht! \n".$filepath.$deletepath);
    }else{
        echo "Kein Rezept zum Löschen angegeben!";
    }
?>