<template>
    <div v-if="show" class="d-flex justify-content-between pb-2 border-bottom">
        <div class="d-block">
            <label class="form-label">Zutat</label>
            <input class="form-control  border-primary" type="text" name="zutat[]" :value="ingcname" required />
        </div>
        <div class="d-block">
            <label class="form-label">Menge</label>
            <input class="form-control border-primary" type="number" name="menge[]" :value="ingcamount" min=0 step=0.1 />
        </div>
            <div class="d-block">
            <label class="form-label">Einheit</label><br>
            <input type="text" name="einheit[]" class="d-none" :value="unit" readonly />
            <div class="btn-group">
                <button type="button" class="btn btn-primary">{{ unit }}</button>
                <div class="btn-group">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"></button>
                    <div class="dropdown-menu dropdown-menu-dark">
                        <button v-for="unit_t in units_t" v-bind:key="unit_t" @click="setUnit" type="button" class="dropdown-item">{{ unit_t }}</button>
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
        show: true,
        ingcname: "",
        ingcamount: 0,
        unit: "g",
        units_t: ["g", "kg", "l", "ml", "Esslöffel", "Teelöffel", "Prise", "Stück", "Packung", "Scheibe"]
    };
  },
  props: {

  },
  methods:{
      setUnit(para){
        console.log(para);
        this.unit = para.target.innerHTML;
      }
  }
}
</script>