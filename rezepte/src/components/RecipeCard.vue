<template>
    <div @click="$emit('show-recipe', card.id)" class="rezeptkarte d-flex border-bottom">
        <div class="h-100 flex-grow-1">
            <h3>{{ card.title }}</h3>
            <div class="d-inline">
                <div v-for="badge in this.badges(this.card)" v-bind:key="badge" class="badge rounded-pill mx-1" :class="badge.class">
                    {{ badge.label }}
                </div>
            </div>
            <p>{{ card.description }}...</p>
        </div>
        <div class="d-flex flex-wrap align-items-center h-100 mw-25">
            <img :src="srcFix(card.image)" alt="Bildchen" @error="imgerr" class="w-100 m-auto img-responsive rounded"/>
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
        id: Number,
        title: String,
        image: String,
        description: String,
        dishtype: String,
        time: Number,
    }
  },
  methods:{
    imgerr(para){
        para.target.className = "d-none";
    },
    // helps to show image, where the local path has been omitted
    srcFix(src){
        if(!src){
            return src;
        }

        if(src.includes("/")){
            return src;
        }
        return "/rezepte/assets/images/" + src;
    },
    badges(rec){
        let ret = [];
        // Speiseart
        ret.push({
            class: "bg-primary",
            label: rec.dishtype
        });
        // Zubereitungszeit
        let hours = Math.floor(rec.time/60);
        let mins = rec.time % 60;
        let timestr = "";
        timestr += hours.toString().padStart(2, "0");
        timestr += ":";
        timestr += mins.toString().padStart(2, "0");

        ret.push({
            class: "bg-light text-dark border",
            label: timestr
        });
        // Allergene
        if(!rec.allergenes){
            return ret;
        }

        ret.push({
            class: "bg-success",
            label: rec.allergenes
        });
        return ret;
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