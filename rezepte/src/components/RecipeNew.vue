<template>
        <dialog ref="dialog" class="p-4 overflow-auto shadow-lg rounded bg-light">
            <div class="d-flex justify-content-between d-print none">
                <h2 class="text-primary flex-grow-1">Neues Rezept</h2>
                <button @click="close" type="button" class="btn-close mt-2" aria-label="Close"></button>
            </div>
            <div v-if="edit_mode" class="alert alert-warning d-flex align-items-center" role="alert">
                <p><b>ACHTUNG: </b>Dieses Formular wird <i class="text-primary">{{ rename }}</i> verändern!</p>
            </div>
            <form @submit="this.submitHandler($event)" class="d-print-none" method="POST" enctype="multipart/form-data">
                <input name="insert" value="recipes" type="text" class="d-none">
                <div class="row gy-2 gx-3 align-items-center">
                    <div class="col-auto">
                        <label for="rezeptname" class="form-label">Rezeptname</label>
                        <input v-if="edit_mode" :value="rename" type="text" class="form-control border-primary" name="rezeptname" readonly required>
                        <input v-else type="text" class="form-control border-primary" name="rezeptname" required>
                        <div id="rezeptnameHelp" class="form-text">Wie heißt dein Rezept?</div>
                    </div>
                    <div class="col-auto">
                        <label for="newamount" class="form-label">Portionen</label>
                        <input v-if="edit_mode" type="number" name="portionen" class="form-control border border-primary" min=1 :value="reamount" required/>
                        <input v-else type="number" name="portionen" class="form-control border border-primary" min=1 required/>
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
                        <input :value="speiseart.id" class="d-none" type="number" name="speiseart" readonly/>
                        <br>
                        <div id="speiseartsel" class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <button id="speiseartselmain" type="button" class="btn btn-primary">{{ speiseart.name }}</button>
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    <button @click="speiseart = foodtype_i" v-for="foodtype_i in metadata['dishtypes']" v-bind:key="foodtype_i" class="dropdown-item" type="button">{{ foodtype_i.name }}</button>
                                </div>
                            </div>
                        </div>
                        <div id="speiseartHelp" class="form-text">Wie wird es serviert?</div>
                    </div>
                    <div class="col-auto">
                        <label for="allergensel" class="form-label">Allergene</label>
                        <div id="allergensel" v-for="allergen in metadata['allergenes']" v-bind:key="allergen" class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" :value="allergen.id" name="allergenes[]" :id="'allergen' + allergen.name + 'switch'">
                            <label class="form-check-label" :for="'allergen' + allergen + 'switch'">{{ allergen.name }}</label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <label for="formreset" class="form-label">Formular säubern</label>
                        <br>
                        <input @click="resetForm" type="reset" class="btn btn-danger" value="Zurücksetzen" />
                        <div id="resetHelp" class="form-text">Ganzes Formular ausleeren</div>
                    </div>
                </div>
                <div class="d-block">
                    <RecipeNewingredient v-for="ingredient in ingredients" v-bind:key="ingredient" :ingredient="ingredient" :units="metadata['units']"/>
                </div>
                <label class="form-label">Zubereitung <i class="text-secondary">kann Markdown!</i></label>
                <div>
                    <div class="form-floating">
                        <textarea v-if="edit_mode"
                            class="form-control border-primary"
                            placeholder="Zubereitung"
                            spellcheck="true"
                            name="zubereitung"
                            :value="redescription"
                            required
                        ></textarea>
                        <textarea v-else
                            class="form-control border-primary"
                            placeholder="Zubereitung"
                            spellcheck="true"
                            name="zubereitung"
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
                            name="bildURL"
                            placeholder="Bildadresse"
                            :value="reimgaddr"
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
                        <input v-if="edit_mode" type="time" name="zubereitungszeit" class="form-control  border-primary without_ampm" id="newTime" step="60" :value="retime" />
                        <input v-else type="time" name="zubereitungszeit" class="form-control  border-primary without_ampm" id="newTime" step="60" value="01:00"/>
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
            <ul>
              <li v-for="log in logs" v-bind:key="log">
                <b>{{ log.severity }}</b> {{ log.msg }}
              </li>
            </ul>
        </dialog>
</template>

<script>

import RecipeNewingredient from './RecipeNewingredient.vue'

export default {
  name: 'NewRecipe',
  data(){
    return {
        show: false,
        edit_mode: false,
        speiseart: {name: "Auswählen", id: -1},
        ingredients: [],
        rename: "",
        reamount: 1,
        redescription: "",
        reglutenfree: false,
        retime: "01:00",
        reimgaddr: "",
        logs: [],
        // Database Info
        dbscript: "rezepte/dist/database.php",
        dburl: window.location.protocol + "//" + window.location.hostname + "/rezepte/",              
    };
  },
  components:{
    RecipeNewingredient
  },
  props: {
    // Verfügbare einheiten, Allergene zum Auswählen
    metadata: Array,
  },
  methods:{
    showDialog(){
        // If unset, set first element
        if(this.speiseart.id == -1){
            this.speiseart = this.metadata["dishtypes"][0];
        }
        this.$refs.dialog.showModal();
    },
    close() {
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.close();
    },
    resetForm(){
        //console.log("Resetting form");
        this.rename = "";
        this.reamount = 1;
        this.redescription = "";
        this.reglutenfree = false;
        this.retime = "01:00";
        this.reimgaddr = "";
        // Reset ingredients:
        this.ingredients = [];
    },
    newIngredient(ingredient = {
            name: "",
            amount: 0,
            unit: "g"
    }){
        this.ingredients.push(ingredient);
        //console.log(this.ingredients);
    },
    setImageUrlInput(para){
        console.log(para.target.files);
        this.reimgaddr = window.location.protocol + "//" + window.location.hostname + "/rezepte/assets/images/" + para.target.files[0].name;
    },
    submitHandler(event){
        const formData = new FormData(event.target);

        const req = new XMLHttpRequest();
        const url = new URL(this.dburl + this.dbscript);
        req.open("POST", url.href);
        req.addEventListener("load", () => {
            console.log(req.responseText);
            let jobj = JSON.parse(req.responseText);
            console.log(jobj);
            if (jobj) {
            this.logs = jobj.logs;
            }
        });
        req.send(formData);
        event.preventDefault();
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

textarea{
    min-height: 100px;
}
</style>