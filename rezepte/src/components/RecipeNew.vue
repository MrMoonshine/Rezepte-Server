<template>
    <div v-if="show" class="position-absolute recipe-form-shadow w-100 top-0 start-0">
        <div class="recipe-form-new w-100 p-4 overflow-auto shadow-lg rounded bg-light position-absolute top-50 start-50 translate-middle">
            <div class="d-flex justify-content-between">
                <h1 class="text-primary flex-grow-1">Neues Rezept</h1>
                <button @click="hideForm" type="button" class="btn-close mt-2" aria-label="Close" />
            </div>
            <form class="d-print-none" action="rezepte/dist/new.php" method="POST" enctype="multipart/form-data">
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
                        <button @click="newIngredient" type="button" class="btn btn-primary"><b>+</b> Neue Zutat</button>
                        <div id="newIngredientHelp" class="form-text">Erweitert die Eingabe</div>
                    </div>
                    <div class="col-auto">
                        <label for="speiseartsel" class="form-label">Speiseart</label>
                        <input :value="foodtype" class="d-none" type="text" name="speiseart" readonly/>
                        <br>
                        <div id="speiseartsel" class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <button id="speiseartselmain" type="button" class="btn btn-primary">{{ foodtypedisplay }}</button>
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    <button @click="setFoodType" v-for="foodtype_i in foodtypes" v-bind:key="foodtype_i" class="dropdown-item" type="button">{{ foodtype_i }}</button>
                                    <span><hr class="dropdown-divider"></span>
                                    <button @click="setFoodType" class="dropdown-item" type="button">Cocktail</button>
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
                        <input @click="resetForm" type="reset" class="btn btn-danger" value="Zurücksetzen" />
                        <div id="resetHelp" class="form-text">Ganzes Formular ausleeren</div>
                    </div>
                </div>
                <div class="d-block">
                    <RecipeNewingredient v-for="ingredient in ingredients" v-bind:key="ingredient"/>
                </div>
                <label class="form-label">Zubereitung</label>
                <div>
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
                            :value="iamgeurl"
                        />
                        <div id="bildURLHelp" class="form-text">Wo ist dein Bild?</div>
                    </div>
                    <div class="col-auto">
                        <label for="bild" class="form-label">Bild vom Gerät Hochladen</label>
                        <input 
                            class="form-control border-primary "
                            type="file"
                            id="bild"
                            name="bild"
                            @change="setImageUrlInput"
                            accept="image/*"
                            data-classIcon="icon-plus"
                            data-buttonText="Your label here."
                        >
                        <div id="newameHelp" class="form-text">Akzeptiert <code>image/*</code></div>
                    </div>
                    <div class="col-auto">
                        <label for="newTime" class="form-label">Zubereitungszeit</label>
                        <input type="time" name="zubereitungszeit" class="form-control  border-primary without_ampm" id="newTime" step="60" value="01:00" />
                        <div class="form-text">hh:mm:--</div>
                    </div>
                    <div class="col-auto">
                        <label for="fertig" class="form-label">Fertig</label>
                        <br>
                        <input type="submit" value="Fertig" class="btn btn-success" />
                        <div class="form-text">Rezept Speichern</div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>

import RecipeNewingredient from './RecipeNewingredient.vue'

export default {
  name: 'NewRecipe',
  data(){
    return {
        show: false,
        ingredients: [],
        iamgeurl: "",
        foodtypes: ["Vorspeise", "Suppe", "Salat", "Hauptspeise", "Nachspeise", "Mehlspeise"],
        foodtype: "",
        foodtypedisplay:"Auswählen"
    };
  },
  components:{
    RecipeNewingredient
  },
  props: {

  },
  methods:{
    showForm(){
        // Empty ingredients
        this.ingredients.length = 0;
        document.getElementsByTagName("body")[0].classList.add("overflow-hidden");
        this.show = true;
    },
    hideForm(){
        document.getElementsByTagName("body")[0].classList.remove("overflow-hidden");
        this.show = false;
    },
    resetForm(){
        this.ingredients.length = 0;
    },
    newIngredient(){
        // Arbitrary value to generate a given amount of ingredients
        this.ingredients.push(1);
    },
    setImageUrlInput(para){
        //console.log(para.target.files);
        this.iamgeurl = "http://" + window.location.hostname + "/rezepte/assets/images/" + para.target.files[0].name;
    },
    setFoodType(para){
        this.foodtype = para.target.innerHTML;
        this.foodtypedisplay = this.foodtype;
    }
  }
}
</script>

<style>
.recipe-form-shadow{
    background-color: rgba(0,0,0,0.5);
    height: 100vh;
    z-index: 100;
}

.recipe-form-new{
    max-width: 55rem;
    max-height: 90vh !important;
    z-index: 101;
}
</style>