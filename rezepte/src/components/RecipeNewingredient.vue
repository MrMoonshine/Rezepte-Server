<template>
    <div v-if="show" class="d-flex justify-content-between pb-2 border-bottom">
        <div class="d-block">
            <label class="form-label">Zutat</label>
            <input class="form-control  border-primary" type="text" name="zutat[]" required="" />
        </div>
        <div class="d-block">
            <label class="form-label">Menge</label>
            <input class="form-control border-primary" type="number" name="menge[]" min="0" step="0.1">
        </div>
            <div class="d-block">
            <input class="d-none" name="einheit[]">
            <label class="form-label">Einheit</label><br>
            <div class="btn-group">
                <input type="button" class="btn btn-primary" value="g">
                <div class="btn-group">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"></button>
                    <div class="dropdown-menu dropdown-menu-dark">
                        <button type="button" class="dropdown-item">g</button>
                        <button type="button" class="dropdown-item">kg</button>
                        <button type="button" class="dropdown-item">l</button>
                        <button type="button" class="dropdown-item">ml</button>
                        <button type="button" class="dropdown-item">Esslöffel</button>
                        <button type="button" class="dropdown-item">Teelöffel</button>
                        <button type="button" class="dropdown-item">prise</button>
                        <button type="button" class="dropdown-item">Stück</button>
                        <button type="button" class="dropdown-item">Packung</button>
                        <button type="button" class="dropdown-item">Scheibe</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-block">
            <label class="form-label">Entfernen</label><br>
            <input @click="show = false" class="btn btn-danger" type="button" value="X" />
        </div>
    </div>
</template>

<script>

export default {
  name: 'RecipeNewingredient',
  data(){
    return {
        show: true
    };
  },
  props: {

  },
  methods:{
  }
}
</script>