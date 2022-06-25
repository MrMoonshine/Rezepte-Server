<template>
  <RecipeDisplay />
  <RecipeSearch />
</template>

<script>
import RecipeDisplay from './components/RecipeDisplay.vue'
import RecipeSearch from './components/RecipeSearch.vue'

export default {
  name: 'App',
  components: {
    RecipeDisplay,
    RecipeSearch
  }
}
</script>