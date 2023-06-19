<?php
define("RECIPE_PV_NAME", "rezeptname");
define("RECIPE_PV_TEXT", "zubereitung");
define("RECIPE_PV_DISHTYPE", "speiseart");
define("RECIPE_PV_INGREDIENT", "zutat");
define("RECIPE_PV_AMOUNT", "menge");
define("RECIPE_PV_UNIT", "einheit");
define("RECIPE_PV_TIME", "zubereitungszeit");
define("RECIPE_PV_IMG_URL", "bildURL");
define("RECIPE_PF_IMG", "bild");

function post_var_safe($name, $default = "")
{
    if (isset($_POST[$name])) {
        return $_POST[$name];
    }
    return $default;
}

function json_var_safe($var, $default = "")
{
    if (isset($var)) {
        return $var;
    }
    return $default;
}

function recipe_parse_time($inp)
{
    $hours = 1;
    $mins = 0;
    if (strlen($inp) > 0) {
        list($hours, $mins) = sscanf($inp, "%d:%d");
    }
    return $hours * 60 + $mins;
}

/*
    @brief Creates a recipe hash to pass on to the database
    @param json_file (optional) path to legacy JSON. Empty string to use POST vars
    @returns hash for database function
*/
function recipe_create($json_file = "")
{
    $ret = array(
        "title" => "",
        "description" => "",
        "dishtype" => 1,
        "dishtype_search" => "",
        "time" => "",
        "url" => "",
        "ingredients" => [],
        "allergenes" => [],
        "allergenes_search" => []
    );

    if (strlen($json_file) > 0) {
        // JSON File
        $jf = file_get_contents($json_file);
        $jobj = json_decode($jf);

        //$ret["title"] = var_export($jobj, true);
        $ret["title"] = json_var_safe($jobj->title);
        $ret["description"] = json_var_safe($jobj->description);
        $ret["dishtype_search"] = json_var_safe($jobj->foodtype);
        $ret["time"] = recipe_parse_time(json_var_safe($jobj->estimatedTime, "01:00"));
        $ret["url"] = json_var_safe($jobj->imageurl);

        if($jobj->glutenFree){
            array_push($ret["allergenes_search"], "Glutenfrei");
        }
    } else {
        // POST var
        $ret["title"] = post_var_safe(RECIPE_PV_NAME);
        $ret["description"] = post_var_safe(RECIPE_PV_TEXT);
        $ret["dishtype"] = post_var_safe(RECIPE_PV_DISHTYPE);
        //  Set time in minutes
        $ret["time"] = recipe_parse_time(post_var_safe(RECIPE_PV_TIME));
        $ret["url"] = post_var_safe(RECIPE_PV_IMG_URL);

        $lenI = count($_POST[RECIPE_PV_INGREDIENT]);
        $lenA = count($_POST[RECIPE_PV_AMOUNT]);
        $lenU = count($_POST[RECIPE_PV_UNIT]);

        // The shortest length counts
        for(
            $i = 0;
            $i < $lenI && $i < $lenA && $i < $lenU;
            $i++
        ){
            array_push(
                $ret["ingredients"], 
                array(
                    "name" => $_POST[RECIPE_PV_INGREDIENT][$i],
                    "amount" => $_POST[RECIPE_PV_AMOUNT][$i],
                    "unit" => intval($_POST[RECIPE_PV_UNIT][$i])
                )
            );
        }

        // Just exit if none are defined
        if(!isset($_POST["allergenes"])){
            return $ret;
        }
        $allergen_count = count($_POST["allergenes"]);
        // Iterate through all Allegenes
        for($a = 0; $a < $allergen_count; $a++){
            $aid = intval($_POST["allergenes"][$a]);
            array_push($ret["allergenes"], $aid);
        }
    }

    return $ret;
}
?>