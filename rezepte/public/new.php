<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta lang="de">

    <!--<link rel="stylesheet" href="styles/bootstrap/spacelab/bootstrap.min.css">-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <title>Alpakaküche</title>
</head>

<body id="body">
    <script>
        var editRecipeURL = "";

        const allrecipes = [ <?php
$dispname;
$fnarr = array();
foreach (glob("assets/*.json") as $filename)
{
    $dispname = substr($filename, 14);
    $dispname = substr($dispname, 0, -5);
    array_push($fnarr, $dispname);
}
for ($x = 0;$x < count($fnarr);$x++)
{
    echo "\"" . $fnarr[$x] . "\"";
    if ($x < count($fnarr) - 1)
    {
        echo ",";
    }
}
//https://img.youtube.com/vi/<insert-youtube-video-id-here>/0.jpg

?>
       ];
        console.log(allrecipes);
    </script>
    <a class="navbar-brand d-none d-print-block" href="#"><img src="/assets/icon.gif" alt="">Alpakaküche</a>
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
    <div class="container">
            <form id="newRecipeForm" class="d-print-none" method="POST" enctype="multipart/form-data">
                <h2 class="text-primary">
                    <?php
$filepath = "/var/www/Rezepte-Server/assets/";
// Set variable $edit if a recipe needs to be replaced
$edit = "";
if (isset($_GET["edit"]))
{
    if (file_exists($filepath . $_GET["edit"]))
    {
        $edit = $_GET["edit"];
        echo ("<i>$edit</i> Bearbeiten");
    }
    else
    {
        echo ("Neues Rezept Hinzufügen");
    }
}
else
{
    echo ("Neues Rezept Hinzufügen");
}
?>
               </h2>
                <div id="replaceinfo" class="d-none">
                    <b class="text-warning">Das Rezept wird <code id="replacerec"></code> ersetzen!</b>
                </div>
                <div class="row gy-2 gx-3 align-items-center">
                    <div class="col-auto">
                        <label for="rezeptname" class="form-label">Rezeptname</label>
                        <input type="text" class="form-control border-primary" id="rezeptname" name="rezeptname"
                            aria-describedby="Rezeptname" required>
                        <div id="rezeptnameHelp" class="form-text">Wie heißt dein Rezept?</div>
                    </div>
                    <div class="col-auto">
                        <label for="newamount" class="form-label">Portionen</label>
                        <input type="number" name="portionen" class="form-control border border-primary" min=1 value=1 required/>
                        <div id="newamountHelp" class="form-text">Für wie viele Personen?</div>
                    </div>
                    <div class="col-auto">
                        <label for="newIngredient" class="form-label">Zutaten</label>
                        <br>
                        <button id="newIngredient" type="button" class="btn btn-primary"><b>+</b> Neue Zutat</button>
                        <div id="newIngredientHelp" class="form-text">Erweitert die Eingabe</div>
                    </div>
                    <div class="col-auto">
                        <label for="speiseartsel" class="form-label">Speiseart</label>
                        <input class="d-none" type="text" id="speiseart" name="speiseart" readonly/>
                        <br>
                        <div id="speiseartsel" class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <button id="speiseartselmain" type="button" class="btn btn-primary">Auswählen</button>
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                <div class="dropdown-menu dropdown-menu-dark" aria-labelledby="btnGroupDrop1">
                                    <button class="dropdown-item" type="button">Vorspeise</button>
                                    <button class="dropdown-item" type="button">Suppe</button>
                                    <button class="dropdown-item" type="button">Salat</button>
                                    <button class="dropdown-item" type="button">Hauptspeise</button>
                                    <button class="dropdown-item" type="button">Nachspeise</button>
                                    <button class="dropdown-item" type="button">Mehlspeise</button>
                                    <span><hr class="dropdown-divider"></span>
                                    <button class="dropdown-item" type="button">Cocktail</button>
                                </div>
                            </div>
                        </div>
                        <div id="speiseartHelp" class="form-text">Wie wird es serviert?</div>
                    </div>
                    <div class="col-auto">
                        <label class="form-check-label" for="gfreesel">Glutenfrei</label>
                        <br>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="glutenfrei" id="glutenfrei">
                        </div>
                        <div id="glutenHelp" class="form-text">Glutenfrei?</div>
                    </div>
                    <div class="col-auto">
                        <label for="formreset" class="form-label">Formular säubern</label>
                        <br>
                        <input id="formreset" type="reset" class="btn btn-danger" value="Zurücksetzen"></input>
                        <div id="resetHelp" class="form-text">Ganzes Formular ausleeren</div>
                    </div>
                    <span><hr class="divider"></span>
                </div>
                <div id="ingredientSpace"></div>
                <script src="js/editor.js"></script>
                <label class="form-label">Zubereitung</label>
                <div>
                    <div class="d-flex">
                        <div class="btn-group m-2" role="group" aria-label="Basic example">
                            <button onclick="editorApplyHeader(1)" type="button" class="btn btn-primary">H<sub>1</sub></button>
                            <button onclick="editorApplyHeader(2)" type="button" class="btn btn-primary">H<sub>2</sub></button>
                            <button onclick="editorApplyHeader(3)" type="button" class="btn btn-primary">H<sub>3</sub></button>
                        </div>
                        <div class="btn-group m-2" role="group" aria-label="Basic example">
                            <button onclick="editorApplyAtStartAndEnd('**')" type="button" class="btn btn-primary"><b>F</b></button>
                            <button onclick="editorApplyAtStartAndEnd('*')" type="button" class="btn btn-primary"><i>K</i></button>
                            <button onclick="editorApplyAtStartAndEnd('`')" type="button" class="btn btn-primary">&lt;/&gt;</button>
                        </div>
                    </div>
                    <div class="form-floating">
                        <textarea
                            class="form-control border-primary"
                            placeholder="Zubereitung"
                            style="height: 100px"
                            spellcheck="true"
                            name="zubereitung"
                            id="zubereitung"
                            required
                        ></textarea>
                        <label for="zubereitung">Zubereitung</label>
                    </div>
                    <div id="zubereitungHelp" class="text-decoration-line-through form-text">**W.I.P** Unterstützt Markdown. Du kannst mit 1., 2., ..., eine Aufzählung machen. Mit minus und leerzeichen dahinter wird eine ungeordnete liste</div>
                </div>
                <div class="row gy-2 gx-3 align-items-center">
                    <div class="col-auto">
                        <label for="bildURL" class="form-label">Rezeptbild-Addresse</label>
                        <input 
                            type="url" 
                            class="form-control  border-primary"
                            id="bildURL"
                            name="bildURL"
                            placeholder="Bildadresse"
                            value=""
                        />
                        <div id="bildURLHelp" class="form-text">Wo ist dein Bild?</div>
                    </div>
                    <div class="col-auto">
                        <label for="bild" class="form-label">Bild vom Gerät Hochladen</label>
                        <input 
                            class="form-control border-primary "
                            type="file"
                            onchange="imageSelected(this.files)"
                            id="bild"
                            name="bild"
                            accept="image/*"
                            data-classIcon="icon-plus"
                            data-buttonText="Your label here."
                        >
                        <div id="newameHelp" class="form-text">Akzeptiert <code>image/*</code></div>
                    </div>
                    <div class="col-auto">
                        <label for="newTime" class="form-label">Zubereitungszeit</label>
                        <input type="time" name="zubereitungszeit" class="form-control  border-primary without_ampm" id="newTime" step="60" value="01:00">
                        <div id="newTimeHelp" class="form-text">hh:mm:--</div>
                    </div>
                    <div class="col-auto">
                        <label for="fertig" class="form-label">Fertig</label>
                        <br>
                        <input id="fertig" type="submit" value="Fertig" class="btn btn-success">
                        <div id="fertigHelp" class="form-text">Rezept Speichern</div>
                    </div>
                </div>
            </form>
            <code id="dump"></code>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous">
    </script>
    <script src="js/newform.js"></script>
<?php
/*
E D I T I N G

*/
if (strlen($edit) > 0)
{
    $erurl = "http://alpakagott/rezepte/assets/" . $edit;
    echo ("<script>");
    echo ("editRecipeURL='" . $erurl . "';\n");
    echo ("</script>");
}
?>
   <script>
        // Form Handling
        if(editRecipeURL.length > 0){
            // Name must not be altered
            document.getElementById("rezeptname").readonly = true;
            // Get JSON DATA
            var xhr = new XMLHttpRequest();
            xhr.open( "GET", editRecipeURL, true ); 
            xhr.send( null );
            xhr.addEventListener("loadend", function(event){
                var robj = JSON.parse(this.responseText);
                fillExistingForm(editRecipeURL.replace("http://" + window.location + "/rezepte/assets/",""), robj);
            });
        }
    </script>
</body>
<?php
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
    echo ("<h1>Error: Zutaten ungültig!</h1>");
}

$jsonstr = json_encode($rezept);
//echo($jsonstr);
// Filehandling
$filename = $filepath . $rezept['title'];

// If editing a file > no renmaing
while (strlen($edit) == 0 && file_exists($filename . ".json"))
{
    // just slap copy on filename lol
    echo ($filename . " already exists. renaming...<br>");
    $filename .= "_copy";
}
$filename .= ".json";
// pwd
//echo getcwd();
//touch($filename) or die("ERROR: Failed to create file! ".$filename."<br>");;
$file = fopen($filename, "wx") or die("ERROR: Failed to write file! " . $filename . "<br>");
// nl2br wegen Zubereitung formatierung
fwrite($file, nl2br($jsonstr));
fclose($file);
//chmod($filename, 777);
echo "<h2 class='text-success'>Rezept wurde Erfolgreich hinzugefügt</h2>";

require ("bgrec.php");
uploadFile("bild");
?>
</html>
