<template>
    <NavBar></NavBar>
    <div class="container">
        <RecipeNew :metadata="metadata" ref="newform" />
        <div v-for="recipe in displayed_recipe" v-bind:key="recipe" class="rezeptanzeige border-bottom d-block d-print-block">
            <!--<DeleteModal v-if="show_delete_modal" @hide-modal="show_delete_modal = false" :url="recipe.delete_link" :name="recipe.data.title" />-->
            <div class="flex-grow-1 mx-2">
                <div class="d-flex justify-content-between">
                    <h1 class="flex-grow-1 text-primary">{{ recipe.title }}</h1>
                    <button @click="displayed_recipe = []" type="button" class="btn-close mt-2" aria-label="Close"></button>
                </div>
                <h3>Für {{ recipe.amount ?? 0 }} Personen</h3>
                <div>
                    <img :src="this.imgurl(recipe.image)" @error="imgerr" class="float-end h-75 img-responsive rounded w-75" alt="" />
                    <Markdown :source="recipe.description" />
                </div>
                <IngredientTable :amount="recipe.amount ?? 1" :ingredients="recipe.ingredients"/>
                <div class="d-flex flex-wrap justify-content-between gap-2 d-print-none">
                    <button @click="printPage" class="btn btn-info w-100 p-2 my-2">Ausdrucken</button>
                    <button @click="edit_recipe(recipe)" class="btn btn-warning flex-fill my-2">Bearbeiten</button>
                    <button @click="show_delete_modal = true" class="btn btn-danger flex-fill my-2">Löschen</button>
                </div>
            </div>
        </div>
        <!--Search-->
        <div class="d-block d-print-none mb-3">
            <AdvancedSearch :metadata="metadata" @filter-update="updateFilter" />
        </div>
        <button @click="showDialog" class="btn btn-success w-100 my-2 d-print-none"><b>+</b> Neues Rezept</button>
        <p><number>{{ rowcount.toString() }}</number></p>
        <LogList :logs="logs"></LogList>
        <PaginationSelection @page-update="pageUpdate" :total="Math.ceil(rowcount / items)" :current="page"></PaginationSelection>
        <div class="d-block d-print-none">
            <!--Foreach Recipe: Create a selection card-->
            <RecipeCard v-for="recipe in recipes" v-bind:key="recipe" :card="recipe" @show-recipe="showRecipe"/>
        </div>
    </div>
</template>

<script>
// Library for markdown:
// https://www.npmjs.com/package/vue3-markdown-it
import Markdown from 'vue3-markdown-it';

// Import Cards
import NavBar from './NavBar.vue'
import RecipeCard from './RecipeCard.vue'
import IngredientTable from './IngredientTable.vue'
import RecipeNew from './RecipeNew.vue'
//import DeleteModal from './DeleteModal.vue'
import AdvancedSearch from './AdvancedSearch.vue'
import PaginationSelection from './PaginationSelection.vue'
import LogList from './LogList.vue'

const DB_SCRIPT = "rezepte/dist/database.php";
const DB_URL = window.location.protocol + "//" + window.location.hostname + "/rezepte/" + DB_SCRIPT;

export default {
  name: 'RecipeSearch',
  components:{
    NavBar,
    RecipeCard,
    IngredientTable,
    RecipeNew,
    //DeleteModal,
    AdvancedSearch,
    Markdown,
    PaginationSelection,
    LogList
},
  data(){
    return {
        displayed_recipe: [],
        recipes: [],                        // Recipe data for the cards
        rowcount: 0,                        // Count of all recipes for a given filter
        matching_recipes: [],
        items_count_select: [16, 32, 64],   // Available items per page setting
        items: 8,                           // Number of items per page
        page: 0,                            // Number of current page (will be diplayed +1)
        show_delete_modal: false,
        logs: [],                            // Logs from database.php
        metadata: []                         // Simple tables ot the DB. e.g ingredients, allergenes
    };
  },
  created(){
    // Initial fetching of items
    this.recipes_get(0, this.items);
    // Get Metadata
    this.fetchMetaData("units");
    this.fetchMetaData("allergenes");
    this.fetchMetaData("dishtypes");
  },
  methods:{
    // Queries database to count all recipes
    recipes_get(page, items, filter = null){
        const url = new URL(DB_URL);
        url.searchParams.append("select", "recipes");
        url.searchParams.append("page", page);
        url.searchParams.append("items", items);
        // Appy filters
        if(filter){
            if(filter.title){
                url.searchParams.append("title", filter.title);
            }
        }

        const req = new XMLHttpRequest();
        req.open("POST", url.href);
        req.addEventListener("load", () => {
        //console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        console.log(jobj);
        if (jobj) {
            // Assign data
            this.rowcount = jobj.rowcount ?? 0;
            this.recipes = jobj.data ?? [];
            this.logs = jobj.logs ?? [];
        }
      });
      req.send();
    },
    // Helper function to catch emit
    pageUpdate(page){
        this.page = page;
        this.recipes_get(page, this.items);
    },
    updateFilter(filter){
        console.log("Updating filter");
        console.log(filter);
        this.recipes_get(0, this.items, filter);
    },
    // show a recipe
    showRecipe(id){
        // scroll to top
        window.scrollTo(0, 0);
        //console.log("Recipe show! " + id);
        const url = new URL(DB_URL);
        url.searchParams.append("select", "recipes");
        url.searchParams.append("id", id);
        const req = new XMLHttpRequest();
        req.open("POST", url.href);
        req.addEventListener("load", () => {
        //console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        console.log(jobj);
        if (jobj) {
            // Assign data
            this.displayed_recipe = jobj.data ?? [];
            this.logs = jobj.logs ?? [];

            if(!jobj.data){
                this.logs.push({
                    severity: "Critical",
                    msg: "Rezept konnte nicht von Datenbank geholt werden!"
                });
            }
        }
      });
      req.send();
    },
    imgurl(src){
        if(!src){
            return "";
        }
        if(src.includes("/")){
            return src;
        }
        return "assets/images/" + src;
    },
    // Hide image if none exists
    imgerr(para){
        para.target.className = "d-none";
    },
    printPage(){
        console.log("Printing");
        if(window.print){
            window.print();
        }else{
            alert("Dein browser kann nicht drucken!");
        }
    },
    showDialog(){
        this.$refs.newform.showDialog();
    },
    // Simple table von datenbank holen
    fetchMetaData(table) {
      const req = new XMLHttpRequest();
      const url = new URL(DB_URL);
      url.searchParams.append("select", table);
      console.log(url);
      req.addEventListener("load", () => {
        //console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        if (jobj) {
          this.metadata[table] = jobj.data;
          //console.log(this.metadata[table]);
        }
      });
      req.open("GET", url.href);
      req.send();
    },
  }
}
</script>