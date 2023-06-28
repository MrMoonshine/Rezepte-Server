<template>
    <NavBar></NavBar>
    <div class="container">
        <RecipeNew @insert-successful="showInsertedRecipe" :metadata="metadata" ref="newform" />
        <div v-for="recipe in displayed_recipe" v-bind:key="recipe" class="rezeptanzeige border-bottom d-block d-print-block">
            <DialogDelete @delete-confirm="this.recipes_delete(recipe.id)" :name="recipe.title" ref="deleter" />
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
                    <button @click="this.showEditDialog(recipe)" class="btn btn-warning flex-fill my-2">Bearbeiten</button>
                    <button @click="showDeleteDialog" class="btn btn-danger flex-fill my-2">Löschen</button>
                </div>
            </div>
        </div>
        <!--Search-->
        <div class="d-block d-print-none mb-3">
            <AdvancedSearch
                :metadata="metadata"
                @filter-update="updateFilter"
                @filter-random="randomRecipe"
            />
        </div>
        <button @click="showDialog" class="btn btn-success w-100 my-2 d-print-none"><b>+</b> Neues Rezept</button>
        <p><number>{{ rowcount.toString() }} Suchergebnisse</number></p>
        <LogList :logs="logs"></LogList>
        <PaginationSelection
            v-if="rowcount == 0 || rowcount > items"
            @page-update="pageUpdate" 
            :total="Math.ceil(rowcount / items)"
            :current="page"
        >
        </PaginationSelection>
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
import DialogDelete from './DialogDelete.vue'
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
    DialogDelete,
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
        filter: null,                       // filter
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
    recipes_receive(req, update_rows = false){
        if(req.status != 200){
            this.logs.push({severity:"Critical",msg:"XHR Error: " + req.status + " " + req.statusText});
            return;
        }
        //console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        console.log(jobj);
        if (jobj) {
            // Assign data
            this.logs = jobj.logs ?? [];
            if(update_rows){
                this.recipes = jobj.data ?? [];
                this.rowcount = jobj.rowcount ?? 0;
            }
        }
    },
    // Queries database to count all recipes
    recipes_get(page, items, filter = null, loadend = null){
        const url = new URL(DB_URL);
        url.searchParams.append("select", "recipes");
        url.searchParams.append("page", page);
        url.searchParams.append("items", items);
        // alter filter if set
        if(filter){
            this.filter = filter;
        }
        // Appy filters
        if(this.filter){
            if(this.filter.title){
                if(this.filter.title.length > 0){
                    url.searchParams.append("title", this.filter.title);
                }                
            }
            if(this.filter.dishtype > 0){
                url.searchParams.append("dishtype", this.filter.dishtype);
            }
            if(this.filter.time){
                if(this.filter.time > 0){
                    url.searchParams.append("time", this.filter.time);
                }
            }

            if(this.filter.allergenes){
                for(const allergene of this.filter.allergenes){
                    url.searchParams.append("allergenes[]", allergene);
                }
            }
            if(this.filter.ingredients){
                for(const ingredient of this.filter.ingredients){
                    url.searchParams.append("ingredients[]", ingredient);
                }
            }
        }

        const req = new XMLHttpRequest();
        req.open("POST", url.href);
        req.addEventListener("load", () => {
            this.recipes_receive(req, true);
        });

        if(loadend){
            req.addEventListener("loadend", loadend);
        }

      req.send();
    },
    recipes_delete(id){
        let formData = new FormData();
        formData.append("delete", "recipes");
        formData.append("id", id);

        const url = new URL(DB_URL);
        const req = new XMLHttpRequest();
        req.open("POST", url.href);
        req.addEventListener("load", () => {
            this.recipes_receive(req);
        });
        req.send(formData);
        // Close recipe view afterwards
        this.displayed_recipe = [];
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
    randomRecipe(filter){
        this.logs = [];
        this.recipes_get(0, this.items, filter, () => {
            if(this.recipes.length < 1){
                this.logs.push({severity: "Warning", msg:"Mit diesem Filter gibt es keine Rezepte."});
                return;
            }
            let min = 0, max = this.rowcount;
            let randid = Math.floor(Math.random() * (max - min) + min);
            randid = randid % this.recipes.length;
            this.showRecipe(this.recipes[randid].id);
        });
    },
    // show recipe after a successful insert
    showInsertedRecipe(title){
        // clear recipe view to see card immediately
        this.displayed_recipe = [];
        // filter for the new/altered recipe via database
        this.recipes_get(
            this.page,
            this.items,
            {
                title: title
            }
        );
        // scroll to top
        window.scrollTo(0, 0);
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
        }else{
            this.logs.push({
              severity: "Critical",
              msg: "JSON parse fehler!"
            });
            console.log(req.responseText);
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
        this.$refs.newform.showDialog(null);
    },
    showEditDialog(recipe){
        this.$refs.newform.showDialog(recipe);
    },
    showDeleteDialog(){
        if(this.$refs.deleter.length > 0){
            this.$refs.deleter[0].showDialog();
        }else{
            console.warn("Delete Dialog nicht gefunden und wird nicht geöffnet.");
        }
    },
    // Simple table von datenbank holen
    fetchMetaData(table) {
      const req = new XMLHttpRequest();
      const url = new URL(DB_URL);
      url.searchParams.append("select", table);
      req.addEventListener("load", () => {
        //console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        if (jobj) {
          this.metadata[table] = jobj.data;
          //console.log(this.metadata[table]);
        }else{
            this.logs.push({
              severity: "Critical",
              msg: "JSON parse fehler!"
            });
            console.log(req.responseText);
        }
      });
      req.open("GET", url.href);
      req.send();
    },
  }
}
</script>