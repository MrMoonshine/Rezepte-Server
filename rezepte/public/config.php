<?php
define("INSTALLATION_PATH", "/var/www/Rezepte-Server/");
define("ASSET_DIR", INSTALLATION_PATH."assets/");
define("SNAPSHOT_DIR", INSTALLATION_PATH."snapshots/");

function sanitize($str_i){
    $str_o = str_replace('"','\"',$str_i);
    $str_o = str_replace("'","\'",$str_o);
    return $str_o;
}
?>