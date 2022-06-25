/*let recipe_arr = [];
const RECIPE_BASE_URL = "http://" + location.hostname + RECIPE_BASE_PATH;
function recipeLoadAll(){
    for(let i of recipe_files){
        var url = RECIPE_BASE_URL + i + ".json";
        var xhr = new XMLHttpRequest();
        xhr.open( "GET", url, true );
        xhr.send(null);

        xhr.addEventListener("loadend", function(event){
            recipe_arr.push(new Recipe(this.responseText));
        });
    }
}
*/
//recipeLoadAll();