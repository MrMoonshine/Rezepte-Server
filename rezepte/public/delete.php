<?php
    $filepath = "/var/www/Rezepte-Server/assets/";
    if(isset($_GET['filename'])){
        $deletepath = $_GET['filename'];
        unlink($filepath.$deletepath);
        echo "<h1>Deleteing:</h1><p>".$deletepath."</p>";
    }else{
        echo "<h1>No Delete Path Set</h1>";
    }
?>
<a href="/rezepte">Back</a>