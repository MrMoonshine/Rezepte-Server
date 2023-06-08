<template>
  <RecipeSearch />
</template>

<script>
import RecipeSearch from './components/RecipeSearch.vue'

export default {
  name: 'App',
  components: {
    RecipeSearch
  }
}
</script>

<style>
a>img{
  max-width: 48px !important;
  max-height: 48px !important;
}

.rezeptanzeige img{
  object-fit: cover;
  max-width: 5cm;
  max-height: 4cm;
}

dialog{
  border: var(--bs-border-width) var(--bs-border-style) var(--bs-border-color);
}

dialog::backdrop{
    background-color: rgba(0,0,0,0.8);
    backdrop-filter: blur(5px);
}
</style>