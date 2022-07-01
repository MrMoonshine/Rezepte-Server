<template>
    <button @click="showForm" class="btn btn-success"><b>+</b> Neues Rezept</button>
    <RecipeNew ref="newform" />
    <div v-for="recipe in displayed_recipe" v-bind:key="recipe" class="rezeptanzeige d-block d-print-block">
        <DeleteModal v-if="show_delete_modal" @hide-modal="show_delete_modal = false" :url="recipe.delete_link" :name="recipe.data.title" />
        <div class="d-flex justify-content-center align-items-center">
            <img :src="recipe.data.imageurl" @error="imgerr" class="h-75 img-responsive rounded w-75"/>
        </div>
        <div class="flex-grow-1 mx-2">
            <div class="d-flex justify-content-between">
                <h1 class="flex-grow-1 text-primary">{{ recipe.data.title }}</h1>
                <button @click="displayed_recipe = []" type="button" class="btn-close mt-2" aria-label="Close"></button>
            </div>
            <h3>Für {{ recipe.data.amount }} Personen</h3>
            <p>{{ recipe.data.description }}</p>
            <IngredientTable :amount="recipe.data.amount" :ingredients="recipe.data.ingredients"/>
            <div class="d-flex flex-wrap justify-content-between d-print-none">
                <button @click="printPage" class="btn btn-info w-100 p-2 m-2">Ausdrucken</button>
                <button @click="edit_recipe(recipe)" class="btn btn-warning flex-fill m-2">Bearbeiten</button>
                <button @click="show_delete_modal = true" class="btn btn-danger flex-fill m-2">Löschen</button>
            </div>
        </div>
    </div>
    <!--Search-->
    <div class="d-block d-print-none mb-3">
        <label for="rezeptsuche" class="fw-bold">Einfache Suche</label>
        <input @input="basic_search" type="search" id="rezeptsuche" placeholder="Rezept Suchen" class="form-control border border-primary"/>
    </div>
    <div class="d-block d-print-none">
        <!--Foreach Recipe: Create a selection card-->
        <RecipeCard v-for="recipe in matching_recipes" v-bind:key="recipe" :card="recipe.card_data" @show-recipe="display_match"/>
    </div>
</template>

<script>
// Import Cards
import RecipeCard from './RecipeCard.vue'
import IngredientTable from './IngredientTable.vue'
import RecipeNew from './RecipeNew.vue'
import DeleteModal from './DeleteModal.vue'

const RECIPE_BASE_PATH = "/rezepte/assets/";
const RECIPE_BASE_URL = "http://" + location.hostname + RECIPE_BASE_PATH;

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
        this.delete_link = "http://"+location.hostname+"/rezepte/rezepte/dist/delete.php?filename=" + this.data.title + ".json";

        //console.table(this.data.ingredients);
    }

    match(criteria = null){
        console.log(criteria);
        if(criteria == null){
            return true;
        }

        if(criteria.search){
            if(criteria.search.length == 0){
                return true;
            }
            return this.data.title.toUpperCase().includes(criteria.search.toUpperCase());
        }
        return true;
    }
}

export default {
  name: 'RecipeSearch',
  components:{
    RecipeCard,
    IngredientTable,
    RecipeNew,
    DeleteModal
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
        this.matching_recipes = [];
        for(let i of this.recipes){
            if(i.match({search: para.target.value})){
                console.log(i.data.title + " matches!");
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
        console.log("Hiding image");
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

<style>
.rezeptanzeige img{
    object-fit: cover;
    max-width: 32rem;
    max-height: 24rem;
}
</style>