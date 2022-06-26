<template>
    <div v-for="recipe in displayed_recipe" v-bind:key="recipe" class="rezeptanzeige d-block">
    <div class="">
      <img :src="recipe.data.imageurl" @error="imgerr" class="h-75 img-responsive rounded img-thumbnail w-100"/>
    </div>
    <div class="flex-grow-1 mx-2">
      <h1 class="text-primary">{{ recipe.data.title }}</h1>
      <h3>Für {{ recipe.data.amount }} Personen</h3>
      <p>{{ recipe.data.description }}</p>
      <IngredientTable :amount="recipe.data.amount" :ingredients="recipe.data.ingredients" />
      <div class="d-flex flex-wrap justify-content-between">
        <button class="btn btn-info w-100 p-2 m-2">Ausdrucken</button>
        <button class="btn btn-warning flex-fill m-2">Bearbeiten</button>
        <button class="btn btn-danger flex-fill m-2">Löschen</button>
      </div>
    </div>
  </div>
    <!--Search-->
    <div class="d-block">
        <label for="rezeptsuche">Such was Oida</label>
        <input type="search" id="rezeptsuche" placeholder="Rezept Suchen" class="form-control border border-primary"/>
    </div>
    <div class="d-block">
        <!--Foreach Recipe: Create a selection card-->
        <RecipeCard v-for="recipe in recipes" v-bind:key="recipe" :card="recipe.card_data" @show-recipe="display_match"/>
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
        //console.table(this.data.ingredients);
    }

    match(criteria = null){
        if(criteria == null){
            return true;
        }

        if(criteria.search){
            return this.data.title.includes(criteria.search);
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
        //console.log(this.recipes);
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