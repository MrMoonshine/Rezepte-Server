<template>
  <dialog ref="dialog" class="p-4 overflow-auto shadow-lg rounded bg-light">
    <div class="d-flex justify-content-between d-print none">
      <h2 class="text-primary flex-grow-1">Neues Rezept</h2>
      <button
        @click="close"
        type="button"
        class="btn-close mt-2"
        aria-label="Close"
      ></button>
    </div>
    <div
      v-if="edit_mode"
      class="alert alert-warning d-flex align-items-center"
      role="alert"
    >
      <p>
        <b>ACHTUNG: </b>Dieses Formular wird
        <i class="text-primary">{{ recipe.title ?? "unknown" }}</i> verändern!
      </p>
    </div>
    <form
      @submit="this.submitHandler($event)"
      class="d-print-none"
      method="POST"
      enctype="multipart/form-data"
    >
      <input name="insert" value="recipes" type="text" class="d-none" />
      <input
        v-if="edit_mode"
        value="1"
        name="edit"
        type="text"
        class="d-none"
      />
      <div class="row gy-2 gx-3 align-items-center">
        <div class="col-auto">
          <label for="rezeptname" class="form-label">Rezeptname</label>
          <input
            type="text"
            v-model="recipe.title"
            class="form-control border-primary"
            name="rezeptname"
            required
          />
          <div id="rezeptnameHelp" class="form-text">
            Wie heißt dein Rezept?
          </div>
        </div>
        <div class="col-auto">
          <label for="newamount" class="form-label">Portionen</label>
          <input
            v-model="recipe.amount"
            type="number"
            name="portionen"
            class="form-control border border-primary"
            min="1"
            required
          />
          <div id="newamountHelp" class="form-text">
            Für wie viele Personen?
          </div>
        </div>
        <div class="col-auto">
          <label for="newIngredient" class="form-label">Zutaten</label>
          <br />
          <button @click="newIngredient" type="button" class="btn btn-primary">
            <b>+</b> Neue Zutat
          </button>
          <div id="newIngredientHelp" class="form-text">
            Erweitert die Eingabe
          </div>
        </div>
        <div class="col-auto">
          <label class="form-label">Speiseart</label>
          <DropdownSelect
            @item-set="setDishtype"
            name="speiseart"
            :options="metadata['dishtypes']"
            ref="inputDishtype"
          ></DropdownSelect>
        </div>
        <div class="col-auto">
          <label for="allergensel" class="form-label">Allergene</label>
          <div
            id="allergensel"
            v-for="allergen in metadata['allergenes']"
            v-bind:key="allergen"
            class="form-check form-switch"
          >
            <input
              class="form-check-input"
              type="checkbox"
              :value="allergen.id"
              name="allergenes[]"
              :id="'allergen' + allergen.name + 'switch'"
              :checked="this.recpieContainsAllergene(allergen.id)"
            />
            <label
              class="form-check-label"
              :for="'allergen' + allergen + 'switch'"
              >{{ allergen.name }}</label
            >
          </div>
        </div>
        <div class="col-auto">
          <label for="formreset" class="form-label">Formular säubern</label>
          <br />
          <input
            @click="resetForm"
            type="reset"
            class="btn btn-danger"
            value="Zurücksetzen"
          />
          <div id="resetHelp" class="form-text">Ganzes Formular ausleeren</div>
        </div>
      </div>
      <div class="d-block">
        <RecipeNewingredient
          v-for="ingredient in recipe.ingredients"
          v-bind:key="ingredient"
          :ingredient="ingredient"
          :units="metadata['units']"
        />
      </div>
      <label class="form-label"
        >Zubereitung <i class="text-secondary">kann Markdown!</i></label
      >
      <div>
        <div class="form-floating">
          <textarea
            v-model="recipe.description"
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
            v-model="recipe.image"
            type="text"
            class="form-control border-primary"
            name="bildURL"
            placeholder="Bildadresse"
          />
          <div id="bildURLHelp" class="form-text">Wo ist dein Bild?</div>
        </div>
        <div class="col-auto">
          <label for="bild" class="form-label">Bild vom Gerät Hochladen</label>
          <input
            class="form-control border-primary"
            type="file"
            id="bild"
            name="bild"
            @change="setImageUrlInput"
            accept="image/*"
            data-classIcon="icon-plus"
            data-buttonText="Your label here."
          />
          <div id="newameHelp" class="form-text">
            Akzeptiert <code>image/*</code>
          </div>
        </div>
        <div class="col-auto">
          <label for="newTime" class="form-label">Zubereitungszeit</label>
          <input
            v-model="recipe.time"
            type="time"
            name="zubereitungszeit"
            class="form-control border-primary without_ampm"
            id="newTime"
            step="60"
          />
          <div class="form-text">hh:mm:--</div>
        </div>
        <div class="col-auto">
          <label for="fertig" class="form-label">Fertig</label>
          <br />
          <input type="submit" value="Fertig" class="btn btn-success" />
          <div class="form-text">Rezept Speichern</div>
        </div>
      </div>
    </form>
    <LogList :logs="logs"></LogList>
  </dialog>
</template>

<script>
import RecipeNewingredient from "./RecipeNewingredient.vue";
import LogList from "./LogList.vue";
import DropdownSelect from "./DropdownSelect.vue";

const EVENT_INSERT_SUCCESSFUL = "insert-successful";

function minutes2time(min_i) {
  // Zubereitungszeit
  let hours = Math.floor(min_i / 60);
  let mins = min_i % 60;
  let timestr = "";
  timestr += hours.toString().padStart(2, "0");
  timestr += ":";
  timestr += mins.toString().padStart(2, "0");
  return timestr;
}

const RECIPE_FALLBACK = {
  title: "",
  amount: 1,
  time: "01:00",
  dishtype: {id: -1, name: "Auswählen"},
  image: "",
  description: "",
  ingredients: [],
  allergenes: [],
};

export default {
  name: "NewRecipe",
  components: {
    RecipeNewingredient,
    LogList,
    DropdownSelect,
  },
  data() {
    return {
      recipe: structuredClone(RECIPE_FALLBACK),
      logs: [],
      // Database Info
      dbscript: "rezepte/dist/database.php",
      dburl:
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/rezepte/",
    };
  },
  props: {
    // Verfügbare einheiten, Allergene zum Auswählen
    metadata: Array,
  },
  methods: {
    // parameter edit is the object of recipe to edit
    showDialog(edit = null) {
      if (edit) {
        this.recipe = edit;
        this.recipe.dishtype = {id: edit.dtid, name: edit.dishtype};
        //Time is stored in js as hh:mm string
        this.recipe.time = minutes2time(edit.time);
        this.edit_mode = true;
      } else {
        this.recipe = structuredClone(RECIPE_FALLBACK);
        this.edit_mode = false;
      }

      // If unset, set first element
      if (this.recipe.dishtype.id < 0) {
        this.setDishtype(this.metadata["dishtypes"][0]);
      }
      this.$refs.inputDishtype.setItem(this.recipe.dishtype);
      this.$refs.dialog.showModal();

      console.log(this.recipe);
    },
    close() {
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.close();
    },
    // Helper function to set the dishtype via the dropdown
    setDishtype(dt) {
      this.recipe.dishtype = dt ?? structuredClone(RECIPE_FALLBACK.dishtype);
      console.log(this.recipe.dishtype);
    },
    //check all allergenes on the recipe if they match the give ID of the checkbox
    recpieContainsAllergene(id) {
      for (const a of this.recipe.allergenes) {
        if (a.id == id) {
          //console.log("Setting allergene " + id);
          return true;
        }
      }
      return false;
    },
    resetForm() {
      this.recipe = structuredClone(RECIPE_FALLBACK);
      this.edit_mode = false;
    },
    newIngredient(
      ingredient = {
        name: "",
        amount: 0,
        unit: "g",
      }
    ) {
      this.recipe.ingredients.push(ingredient);
      //console.log(this.ingredients);
    },
    setImageUrlInput(para) {
      this.recipe.image = para.target.files[0].name;
    },
    submitHandler(event) {
      const formData = new FormData(event.target);

      const req = new XMLHttpRequest();
      const url = new URL(this.dburl + this.dbscript);
      req.open("POST", url.href);
      req.addEventListener("load", () => {
        if (req.status != 200) {
          this.logs.push({
            severity: "Critical",
            msg: "XHR Error: " + req.status + " " + req.statusText,
          });
          return;
        }
        console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        console.log(jobj);
        if (jobj) {
          this.logs = jobj.logs;

          if (jobj.statusId >= 0) {
            this.$emit(EVENT_INSERT_SUCCESSFUL, this.recipe.title ?? "");
            if (this.logs.length < 1) {
              this.close();
            } else {
              this.logs.push({
                severity: "Info",
                msg: "Rezept wurde erfolgreich hinzugefügt.",
              });
            }
          }
        }
      });
      req.send(formData);
      event.preventDefault();
    },
  },
};
</script>

<style>
.recipe-form-shadow {
  background-color: rgba(0, 0, 0, 0.5);
  height: 100vh;
  z-index: 100;
}

.recipe-form-new {
  max-width: 55rem;
  max-height: 90vh !important;
  z-index: 101;
}

textarea {
  min-height: 100px;
}
</style>
