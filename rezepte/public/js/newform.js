var nrecipe = {amount:0,title:"recipe",speiseart:"",ingredients:[{name:0,amount:0,unit:"none"}],description:"i am a recipe!",imageurl:"none",imgType:"none",glutenFree:false,estimatedTime:null};

var p_units = ["g","kg","l","ml","Esslöffel","Teelöffel","prise","Stück","Packung","Scheibe"];
var geninp = ["name","amount","unit"];
var ingCount = 0;
const ingprefix = "ingredientFormDivNumber";

function buildUnitSel(data){
    var seldiv = document.createElement("div");
    seldiv.className += "col-auto";

    var label = document.createElement("label");
    label.className += "form-label";
    label.innerHTML = "Einheit";

    var group = document.createElement("div");
    group.className += "btn-group";
    group.role = "group";

    var unitinput = document.createElement("input");
    unitinput.className += "d-none";
    unitinput.value = "g";
    unitinput.name = "einheit[]";

    var ddgroup = document.createElement("div");
    ddgroup.className += "btn-group";
    ddgroup.role = "group";

    var ddli = document.createElement("div");
    ddli.className += "dropdown-menu dropdown-menu-dark";

    var mainb = document.createElement("input");
    mainb.type = "button";
    mainb.className += "btn btn-primary";
    mainb.value = p_units[0];

    var ddb = document.createElement("button");
    ddb.className += "btn btn-primary dropdown-toggle";
    ddb.type = "button";
    ddb.dataset.bsToggle = "dropdown";

    for(let i = 0; i < p_units.length; i++){
        var ddlit = document.createElement("button");
        ddlit.type = "button";
        ddlit.className += "dropdown-item";
        
        ddlit.innerHTML = p_units[i];
        ddlit.onclick = function(){
            mainb.value = this.innerHTML;
            unitinput.value = this.innerHTML;
        }
        ddli.appendChild(ddlit);
    }

    ddgroup.appendChild(ddb);
    ddgroup.appendChild(ddli);

    
    group.appendChild(mainb);
    group.appendChild(ddgroup);

    if(data){
        mainb.value = data.unit;
    }

    seldiv.append(unitinput);
    seldiv.appendChild(label);
    seldiv.appendChild(document.createElement("br"));
    seldiv.appendChild(group);
    return seldiv;
}

function addIng(data = null){
    var main = document.createElement("div");
    main.className += "row gy-2 gx-3 align-items-center ingredientforminput";
    main.id = ingprefix + ingCount;

    var zutatlabel = document.createElement("label");
    zutatlabel.className += "form-label";
    zutatlabel.innerHTML = "Zutat";
    var zutat = document.createElement("input");
    zutat.className += "form-control  border-primary";
    zutat.type = "text";
    zutat.name = "zutat[]";
    zutat.required = true;
    zutat.onchange = validateAll;
    var zutatdiv = document.createElement("div");
    zutatdiv.appendChild(zutatlabel);
    zutatdiv.appendChild(zutat);
    zutatdiv.className += "col-auto";
    main.appendChild(zutatdiv);

    var mengelabel = document.createElement("label");
    mengelabel.className += "form-label";
    mengelabel.innerHTML = "Menge";
    var menge = document.createElement("input");
    menge.className += "form-control border-primary";
    menge.type = "number";
    menge.name = "menge[]";
    menge.value = 0;
    menge.onchange = validateAll;
    var mengediv = document.createElement("div");
    mengediv.appendChild(mengelabel);
    mengediv.appendChild(menge);
    mengediv.className += "col-auto";
    main.appendChild(mengediv);

    main.appendChild(buildUnitSel(data));

    var deletelabel = document.createElement("label");
    deletelabel.className += "form-label";
    deletelabel.innerHTML = "Entfernen";
    var deleteb = document.createElement("input");
    deleteb.className += "btn btn-danger";
    deleteb.value = "X";
    deleteb.type = "button";
    deleteb.onclick = function(){
        document.getElementById("ingredientSpace").removeChild(
            document.getElementById(main.id)
        );
    }
    //console.log(deleteb);
    var deletediv = document.createElement("div");
    deletediv.appendChild(deletelabel);
    deletediv.appendChild(document.createElement("br"));
    deletediv.appendChild(deleteb);
    deletediv.className += "col-auto";
    main.appendChild(deletediv);

    var spant = document.createElement("span");
    spant.className += "recend";
    spant.appendChild(document.createElement("hr"));
    main.appendChild(spant);

    if(data){
        zutat.value = data.name;
        menge.value = data.amount;
    }

    ingCount++;
    return main;
}

function validate(id){
    var valid = false;
    if(document.getElementById(id) == null){
        console.warn("Element " + id + " doesn't exist!");
        return false;
    }else{
        if(document.getElementById(id).type == "text" || document.getElementById(id).type == "url"){
            valid = document.getElementById(id).value.length > 0;
        }else if(document.getElementById(id).type == "number"){
            valid = document.getElementById(id).value > 0;
        }else if(document.getElementById(id).tagName == "textarea".toUpperCase()){
            valid = document.getElementById(id).value.length > 0;
        }else{
            console.warn("Element " + id + " can't be validated");
            return false;
        }
    }

    document.getElementById(id).classList.remove("border-primary");
    if(valid){
        document.getElementById(id).classList.remove("is-invalid");
        document.getElementById(id).className += " is-valid";
    }else{
        document.getElementById(id).classList.remove("is-valid");
        document.getElementById(id).className += " is-invalid";
    }
    console.log(id + " valid: " + valid);
    return valid;
}

function validateIngredientNames(){
    var ret = true;
    for(let j of document.getElementsByClassName("ingredientforminput")){
        for(let i of j.getElementsByTagName("input")){
            i.classList.remove("border-primary");
            var valid = false;

            if(i.type == "text"){
                valid = i.value.length > 0;
            }else if(i.type == "number"){
                valid = i.value > 0;
            }

            if(i.type == "text" || i.type == "number"){
               if(!valid){
                    i.classList.remove("is-valid");
                    i.className += " is-invalid";
                    ret = false;
                }else{
                    i.classList.remove("is-invalid");
                    i.className += " is-valid";
                } 
            }
            
        }   
    }
    return ret;
}

function unsetValidation(){
    for(let i of document.getElementsByClassName("is-valid")){
       i.classList.remove("is-valid");
       i.className += " border-primary";
    }

    for(let i of document.getElementsByClassName("is-invalid")){
        i.classList.remove("is-invalid");
        i.className += " border-primary";
    }
}

function validateAll(){
    var valid = true;
    valid &= validate("zubereitung");
    valid &= validate("rezeptname");
    valid &= validateIngredientNames();
    return valid;
}

function buildspeiseartsel(){
    for(let i of document.getElementById("speiseartsel").getElementsByClassName("dropdown-item")){
        i.onclick = function(){
            document.getElementById("speiseartselmain").innerHTML = this.innerHTML;
            document.getElementById("speiseart").value = this.innerHTML;
        };
    }
}

function imageSelected(files){
    console.log(files)
    document.getElementById("bildURL").readonly = true;
    document.getElementById("bildURL").value = "http://" + location.hostname + "/rezepte/assets/images/" + files[0].name;
}

function getIngredients(){
    var ret = [];
    for(let i of document.getElementsByClassName("ingredientforminput")){
        var inputs = i.querySelectorAll("input[type=number]");
        //console.log(inputs);
        ret.push({
            name:i.querySelectorAll("input[type=text]")[0].value,
            amount:Number(i.querySelectorAll("input[type=number]")[0].value),
            unit:i.querySelectorAll("input[type=button]")[0].value
        });
    }
    return ret;
}

function getMeta(ingredients){
    //Copy empty recipe values into this one
    var recipe  = Object.assign({},nrecipe);
    recipe.ingredients = ingredients;

    recipe.title = document.getElementById("rezeptname").value;
    recipe.description = document.getElementById("zubereitung").value;
    if(!recipe.description){
        recipe.description = "Kochen";
    }
    recipe.glutenFree = document.getElementById("glutenfrei").checked;

    recipe.amount = Number(personInput.getInput().value,10);

    recipe.imageurl = document.getElementById("bildURL").value;
    if(recipe.imageurl.includes("youtube")){
        recipe.imgType = "video-url";
    }else{
        recipe.imgType = "url";
    }

    recipe.estimatedTime = document.getElementById("newTime").value;

    recipe.speiseart = document.getElementById("speiseartselmain").innerHTML;
    if(recipe.speiseart == "Auswählen"){
        recipe.speiseart = "";
    }

    return recipe;
}

function showReplaceInfo(show){
    if(show){
        document.getElementById("replaceinfo").classList.remove("d-none");
        document.getElementById("replaceinfo").className += " d-block";
    }else{
        document.getElementById("replaceinfo").classList.remove("d-block");
        document.getElementById("replaceinfo").className += " d-none";
    }
}

function resetForm(){
    document.getElementById("replacerec").innerHTML = "";
    document.getElementById("ingredientSpace").innerHTML = "";
    document.getElementById("portionen").value = 1;
    unsetValidation();

    //Is an imge selected?
    document.getElementById("bildURL").disabled = false;

    showReplaceInfo(false);

    ingCount = 0;
}

//Edit Recipes
function fillExistingForm(filename,robj){
    resetForm();
    //console.log("Filling form for: " + robj.title);

    document.getElementById("replacerec").innerHTML = filename;
    showReplaceInfo(true);

    document.getElementById("rezeptname").value = robj.title;
    document.getElementById("portionen").value = robj.amount;

    if(robj.glutenFree){
        document.getElementById("glutenfrei").checked = true;
    }

    if(robj.estimatedTime){
        document.getElementById("newTime").value = robj.estimatedTime;
    }else{
        document.getElementById("newTime").value = "01:00";
    }

    if(robj.speiseart){
        if(robj.speiseart.length > 0){
            document.getElementById("speiseartselmain").innerHTML = robj.speiseart;
        }
    }

    if(robj.imageurl){
        document.getElementById("bildURL").value = robj.imageurl;
        document.getElementById("bild").value = null;
    }

    document.getElementById("zubereitung").value = robj.description;

    for(let i of robj.ingredients){
        console.log(i);
        document.getElementById("ingredientSpace").appendChild(addIng(i));
    }
}

buildspeiseartsel();

document.getElementById("newIngredient").onclick = function(){
    document.getElementById("ingredientSpace").appendChild(addIng());
    //console.log(document.getElementById(ingprefix + (ingCount-1)));
}
//Delete All Ingredients
document.getElementById("formreset").onclick = resetForm;

//Is an imge selected?
document.getElementById("bildURL").disabled = document.getElementById("bild").files.length > 0;

// Form Validation before submit
document.getElementById("newRecipeForm").addEventListener("submit", function(event){
    var valid = validateAll();
    if(!valid){
        event.preventDefault();
    }
    return valid;
});