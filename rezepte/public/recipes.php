<?php
// Prevent warning in browser
header('Content-type: text/javascript');
// Create Array
$dispname;
$fnarr = array();
$base_path = "/var/www/Rezepte-Server/assets/";

echo("window.recipe_files = [");
foreach(glob($base_path."*.json") as $filename) {
    $dispname = substr($filename, strlen($base_path));
    $dispname = substr($dispname, 0, -5);
    array_push($fnarr, $dispname);
}
for ($x = 0; $x < count($fnarr); $x++) {
    echo "\"".$fnarr[$x]."\"";
    if ($x < count($fnarr) - 1) {
        echo ",";
    }
}
//https://img.youtube.com/vi/<insert-youtube-video-id-here>/0.jpg

echo("];\nconsole.log(recipe_files);\n");
?>
// Global vars
window.foodtypes = ["Vorspeise", "Suppe", "Salat", "Hauptspeise", "Nachspeise", "Mehlspeise"];