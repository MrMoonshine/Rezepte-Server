<template>
    <div @click="$emit('show-recipe', card)" class="rezeptkarte d-flex border-bottom">
        <div class="h-100 flex-grow-1">
            <h3>{{ card.title }}</h3>
            <div class="d-inline">
                <div v-for="badge in card.badges" v-bind:key="badge" class="badge rounded-pill mx-1" :class="badge.class">
                    {{ badge.label }}
                </div>
            </div>
            <p>{{ card.description }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center h-100 mw-25">
            <img :src="card.image" alt="Bildchen" @error="imgerr" class="w-100 m-auto img-responsive rounded"/>
        </div>
    </div>
</template>

<script>

export default {
  name: 'RecipeCard',
  data(){
    return {};
  },
  props: {
    card: {
        title: String,
        image: String,
        description: String,
        badges: [
            {label: String, class: String}
        ]
    }
  },
  methods:{
    imgerr(para){
        console.log("Hiding image");
        para.target.className = "d-none";
    }
  }
}
</script>

<style>
.rezeptkarte{
    height: 15rem !important;
}

.rezeptkarte img{
    height: 12rem;
}

img{
    object-fit: cover !important;
}
</style>