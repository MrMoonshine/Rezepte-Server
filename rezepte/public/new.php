<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta lang="de">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <title>Alpakaküche</title>
</head>

<body id="body">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary d-flex d-print-none">
        <a class="navbar-brand" href="#"><img src="/assets/icon.gif" alt="">Alpakaküche</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarToggleExternalContent">
            <div class="navbar-nav">
                <a class="nav-item nav-link" href="/rezepte">Rezepte </a>
                <a class="nav-item nav-link active" href="#">Neues Rezept</a>
                <a class="nav-item nav-link" href="/">Eingangshalle</a>
            </div>
        </div>

    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>
</body>
<?php
var_dump($_POST);
$filepath = "/var/www/Rezepte-Server/assets/";
/*
C R E A T I N G

*/
//echo var_dump($_POST);
// Basic Error Handling
isset($_POST["rezeptname"]) or exit();
isset($_POST["zubereitung"]) or die("<h1>Error: Keine Zubereitungsanleitung!</h1>");
isset($_POST["portionen"]) or die("<h1>Error: Fehler bei Portionen!</h1>");
// Finally create recipe
$rezept['title'] = $_POST["rezeptname"];
$rezept['description'] = $_POST["zubereitung"];
$rezept['amount'] = $_POST["portionen"];
// Art
if (isset($_POST["speiseart"]))
{
    $rezept['foodtype'] = $_POST["speiseart"];
}
else
{
    $rezept['foodtype'] = "";
}
// Glutenfrei
$rezept['glutenFree'] = isset($_POST["glutenfrei"]);
// Zeit
if (isset($_POST["zubereitungszeit"]))
{
    $rezept['estimatedTime'] = $_POST["zubereitungszeit"];
}
// Image URL
if (isset($_POST["bildURL"]))
{
    $rezept['imageurl'] = $_POST["bildURL"];
}
else
{
    $rezept['imageurl'] = "";
}
$rezept['imgType'] = "url";
// Zutaten
if (
// Longer than 0 and all are equally long
count($_POST["zutat"]) > 0 && count($_POST["zutat"]) == count($_POST["menge"]) && count($_POST["menge"]) == count($_POST["einheit"]))
{
    $arr = array();
    for ($i = 0;$i < count($_POST["zutat"]);$i++)
    {
        $zutat['name'] = $_POST["zutat"][$i];
        $zutat['amount'] = (int)($_POST["menge"][$i]);
        $zutat['unit'] = $_POST["einheit"][$i];
        array_push($arr, $zutat);
    }

    $rezept['ingredients'] = $arr;
}
else
{
    echo ("<h1 class\"text-danger\">Error: Zutaten ungültig!</h1>");
}

$jsonstr = json_encode($rezept);
//echo($jsonstr);
// Filehandling
$filename = $filepath . $rezept['title'];

$filename .= ".json";
// pwd
//echo getcwd();
//touch($filename) or die("ERROR: Failed to create file! ".$filename."<br>");;
$file = fopen($filename, "wx") or die("ERROR: Failed to write file! " . $filename . "<br>");
file_put_contents($filename, "");
// nl2br wegen Zubereitung formatierung
fwrite($file, nl2br($jsonstr));
fclose($file);
//chmod($filename, 777);
echo "<h2 class='text-success'>Rezept wurde Erfolgreich hinzugefügt</h2>";
echo("<p>Rezept wurde hier gespeichert: ".$filename."</p>");

require ("bgrec.php");
uploadFile("bild");
?>
</html>
