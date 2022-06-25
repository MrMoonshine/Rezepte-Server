<template>
    <div class="d-block">
        <label for="rezeptsuche">Such was Oida</label>
        <input type="search" id="rezeptsuche" placeholder="Rezept Suchen" class="form-control border border-primary"/>
    </div>
    <button @click="loadAll" class="btn btn-primary">Oida</button>
    <div class="rezeptkarte d-block">
        <div v-for="recipe in recipes" v-bind:key="recipe" class="row border-bottom">
            <div class="col-3 text-center">
                <img v-bind:src="recipe.data.imageurl" alt="Bildchen" class="my-2 h-75 img-responsive rounded img-thumbnail"/>
            </div>
            <div class="col-6">
                <h3>{{ recipe.data.title }}</h3>
                <div class="d-inline">
                    <div v-for="badge in recipe.card_tags" v-bind:key="badge" class="badge rounded-pill mx-1" :class="badge.class">
                        {{ badge.label }}
                    </div>
                </div>
                <p>{{ recipe.card_description }}</p>
            </div>
        </div>
    </div>
</template>

<script>
const RECIPE_BASE_PATH = "/rezepte/backend/files/";
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
        if(this.data.estimatedTime){
            this.card_tags.push({label: this.data.foodtype, class: "text-bg-primary"});
        }

        //console.log(this);
    }

    get match(){
        return true;
    }
}

export default {
  name: 'RecipeSearch',
  data(){
    return {
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
    // Don't display Images at recipes which havent one configured
    hideOnError(event){
        console.log(event.target);
        event.target.src = "http://alpakagott/assets/backgrounds/2b.webp";
    }
  }
}
</script>

<style>
.rezeptkarte{
    height: 12rem !important;
}

img{
    object-fit: cover !important;
}
</style>