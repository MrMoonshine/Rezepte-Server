<template>
    <NavBar></NavBar>
    <RecipeNew ref="newform" />
    <div v-for="recipe in displayed_recipe" v-bind:key="recipe" class="rezeptanzeige border-bottom d-block d-print-block">
        <DeleteModal v-if="show_delete_modal" @hide-modal="show_delete_modal = false" :url="recipe.delete_link" :name="recipe.data.title" />
        <div class="flex-grow-1 mx-2">
            <div class="d-flex justify-content-between">
                <h1 class="flex-grow-1 text-primary">{{ recipe.data.title }}</h1>
                <button @click="displayed_recipe = []" type="button" class="btn-close mt-2" aria-label="Close"></button>
            </div>
            <h3>Für {{ recipe.data.amount }} Personen</h3>
            <div>
                <img :src="recipe.data.imageurl" @error="imgerr" class="float-end h-75 img-responsive rounded w-75" alt="" />
                <Markdown :source="recipe.data.description" />
            </div>
            <IngredientTable :amount="recipe.data.amount" :ingredients="recipe.data.ingredients"/>
            <div class="d-flex flex-wrap justify-content-between gap-2 d-print-none">
                <button @click="printPage" class="btn btn-info w-100 p-2 my-2">Ausdrucken</button>
                <button @click="edit_recipe(recipe)" class="btn btn-warning flex-fill my-2">Bearbeiten</button>
                <button @click="show_delete_modal = true" class="btn btn-danger flex-fill my-2">Löschen</button>
            </div>
        </div>
    </div>
    <button @click="showForm" class="btn btn-success w-100 my-3 d-print-none"><b>+</b> Neues Rezept</button>
    <!--Search-->
    <div class="d-block d-print-none mb-3">
        <label for="rezeptsuche" class="fw-bold">Einfache Suche</label>
        <div class="input-group">
            <input @input="basic_search" type="search" id="rezeptsuche" placeholder="Rezept Suchen" class="form-control border border-primary"/>
            <button class="input-group-text btn btn-primary dropdown-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#adv-search-form" aria-expanded="false" aria-controls="adv-search-form">Advanced</button>
        </div>
        <div class="collapse" id="adv-search-form">
            <AdvancedSearch @advanced-search="(cri) => advanced_search(cri)" />
        </div>
    </div>
    <div class="d-block d-print-none">
        <!--Foreach Recipe: Create a selection card-->
        <RecipeCard v-for="recipe in matching_recipes" v-bind:key="recipe" :card="recipe.card_data" @show-recipe="display_match"/>
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
import DeleteModal from './DeleteModal.vue'
import AdvancedSearch from './AdvancedSearch.vue'

const RECIPE_BASE_PATH = "/rezepte/assets/";
const RECIPE_BASE_URL = window.location.protocol + "//" + window.location.hostname + RECIPE_BASE_PATH;

class Recipe{
    constructor(json_string){
        this.data = JSON.parse(json_string);
        this.card_description = this.data.description.substring(0,100) + "...";
        this.card_tags = [];
        // Tag Glutenfrei
        if(this.data.glutenFree){
            this.card_tags.push({label: "Glutenfei", class: "text-bg-success"});
        }
        // Tag Zeit
        if(this.data.estimatedTime){
            this.card_tags.push({label: this.data.estimatedTime, class: "text-bg-danger"});
        }
        // Tag Art
        if(this.data.foodtype){
            this.card_tags.push({label: this.data.foodtype, class: "text-bg-primary"});
        }

        this.card_data = {
            title: this.data.title,
            image: this.data.imageurl,
            description: this.card_description,
            badges: this.card_tags
        };

        // Delete Link
        this.delete_link = location.protocol + "//" + location.hostname + "/rezepte/rezepte/dist/delete.php?filename=" + this.data.title + ".json";

        //console.table(this.data.ingredients);
    }

    match(criteria = null){
        //console.log(criteria);
        if(criteria == null){
            return true;
        }

        if(criteria.search){
            if(criteria.search.length == 0){
                return true;
            }
            return this.data.title.toUpperCase().includes(criteria.search.toUpperCase());
        }

        var matchName = true;
        if(criteria.name){
            if(criteria.name.length > 0){
                /*
                    Warum include und kein exact match?
                    Use Case: Was mit Mehl kochen/backen
                    Dann schreibt wer stadessen:
                    - Weizenmehl
                    Diese Wörter werden nicht getrennt geschrieben und können somit nicht einzeln überprüft werden
                */
                matchName = this.data.title.toUpperCase().includes(criteria.name.toUpperCase());
            }
        }

        var matchFoodType = true;
        if(criteria.foodtype && this.data.foodtype){
            if(criteria.foodtype.length > 0){
                matchFoodType = this.data.foodtype.toUpperCase().includes(criteria.foodtype.toUpperCase());
            }
        }else if(!this.data.foodtype){
            // show if none is selected
            matchFoodType = criteria.foodtype.length == 0;
        }

        var matchTime = true;
        if(criteria.time && this.data.estimatedTime){
            console.log(criteria.time);
            if(criteria.time.length > 0){
                var basedate = "January 1, 2000 ";
                var sdate = new Date(basedate + criteria.time);
                var rdate = new Date(basedate + this.data.estimatedTime);
                // Compare:
                if(rdate > sdate){
                    matchTime = false;
                }
            }
        }

        var matchIngredients = true;
        if(criteria.whitelist){
            for(let i of criteria.whitelist){
                var found = false;
                for(let j of this.data.ingredients){
                    found = found || j.name.toUpperCase().includes(i.toUpperCase());
                }
                matchIngredients = matchIngredients && found;
            }
        }

        return matchName && matchFoodType && matchTime && matchIngredients;
    }
}

export default {
  name: 'RecipeSearch',
  components:{
    NavBar,
    RecipeCard,
    IngredientTable,
    RecipeNew,
    DeleteModal,
    AdvancedSearch,
    Markdown,
},
  data(){
    return {
        displayed_recipe: [],
        recipes: [],
        matching_recipes: [],
        items: [{ message: 'Foo' }, { message: 'Bar' }],
        show_delete_modal: false
    };
  },
  methods:{
    search(sstr){
        console.log(sstr);
    },
    loadAll(){
        for(let i of window.recipe_files){
            var url = RECIPE_BASE_URL + i + ".json";
            var xhr = new XMLHttpRequest();
            xhr.open( "GET", url, false );
            
            xhr.send(null);
            this.recipes.push(new Recipe(xhr.responseText));
        }

        this.matching_recipes = [...this.recipes];
        //console.log(this.recipes);
    },
    basic_search(para){
        console.log(para.target.value);
        this.advanced_search({search: para.target.value});
    },
    advanced_search(para){
        this.matching_recipes = [];
        for(let i of this.recipes){
            if(i.match(para)){
                this.matching_recipes.push(i);
            }
        }
    },
    display_match(card){
        // empty array
        this.displayed_recipe.length = 0;
        // hide delete modal:
        this.show_delete_modal = false;
        // match
        for(let i of this.recipes){
            if(i.data.title == card.title){
                this.displayed_recipe.push(i);
            }
        }
        // Scroll to top
        window.scrollTo(0, 0);
    },
    edit_recipe(rec){
        this.$refs.newform.showFormEdit(rec);
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
    showForm(){
        this.$refs.newform.showForm();
    }
  },
    beforeMount(){
        this.loadAll();
    }
}
</script>