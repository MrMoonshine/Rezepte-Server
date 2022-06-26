<template>
    <div v-for="recipe in displayed_recipe" v-bind:key="recipe" class="rezeptanzeige d-block d-print-block">
    <div class="d-flex justify-content-center align-items-center">
        <img :src="recipe.data.imageurl" @error="imgerr" class="h-75 img-responsive rounded w-75"/>
    </div>
    <div class="flex-grow-1 mx-2">
        <div class="d-flex justify-content-between">
            <h1 class="flex-grow-1 text-primary">{{ recipe.data.title }}</h1>
            <button @click="displayed_recipe = []" type="button" class="btn-close" aria-label="Close"></button>
        </div>
        <h3>Für {{ recipe.data.amount }} Personen</h3>
        <p>{{ recipe.data.description }}</p>
        <IngredientTable :amount="recipe.data.amount" :ingredients="recipe.data.ingredients" />
        <div class="d-flex flex-wrap justify-content-between d-print-none">
            <button @click="printPage" class="btn btn-info w-100 p-2 m-2">Ausdrucken</button>
            <a :href="recipe.edit_link" class="btn btn-warning flex-fill m-2">Bearbeiten</a>
            <a :href="recipe.delete_link" class="btn btn-danger flex-fill m-2">Löschen</a>
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

        // Edit Link
        this.edit_link = "/rezepte/rezepte/dist/new.php?edit=" + this.data.title + ".json";
        // Delete Link
        this.delete_link = "/rezepte/rezepte/dist/delete.php?filename=" + this.data.title + ".json";

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
    IngredientTable
  },
  data(){
    return {
        displayed_recipe: [],
        recipes: [],
        matching_recipes: [],
        items: [{ message: 'Foo' }, { message: 'Bar' }]
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
        // match
        for(let i of this.recipes){
            if(i.data.title == card.title){
                this.displayed_recipe.push(i);
            }
        }
        // Scroll to top
        window.scrollTo(0, 0);
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