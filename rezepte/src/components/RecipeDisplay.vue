<template>
  <div class="flex flex-wrap">
    <div class="rezeptanzeige p-2">
        <img :src="testimg" alt="..." class="w-100 rounded">
    </div>
    <div class="flex-grow-1">
        <h1>{{ recipename }}</h1>
    </div>
  </div>
  <button @click="greet">Oida</button>
</template>

<script>
export default {
  name: 'RecipeDisplay',
  data(){
    return {
    recipename: "Test Oida",
    testimg: "http://alpakagott/assets/chonkerwisdom.webp"};
  },
  methods:{
    greet(recipe){
      this.recipename = "Bam Oida";
      console.log(this);
      console.log(recipe);
    }
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