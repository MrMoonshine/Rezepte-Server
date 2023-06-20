<template>
    <form ref="form" class="border">
          <h2>Suche</h2>
          <p>Jedes Feld was du ausfüllst ist für die Suche relevant. Alle anderen sind egal</p>
          <div class="d-flex flex-wrap justify-content-between gap-2 pb-3 border-bottom">
            <div class="flex-grow-1">
              <label>Name:</label>
              <input name="title" type="search" placeholder="Name" class="form-control border border-primary"/>
            </div>
            <div class="d-block">
              <label>Speiseart:</label>
              <br>
              <input :value="foodtype" class="d-none" type="text" name="speiseart" readonly/>
              <div id="speiseartsel" class="btn-group" role="group" aria-label="Button group with nested dropdown">
                <button id="speiseartselmain" type="button" class="btn btn-primary">{{ foodtypedisplay }}</button>
                  <div class="btn-group" role="group">
                    <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle"
                      data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                    <button @click="setFoodType" v-for="foodtype_i in foodtypes" v-bind:key="foodtype_i" class="dropdown-item" type="button">{{ foodtype_i }}</button>
                    <span><hr class="dropdown-divider"></span>
                    <button @click="setFoodType" class="dropdown-item" type="button">Cocktail</button>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <label>Zutat Filter +</label>
              <br>
              <button @click="add_whitelist_input" type="button" class="btn btn-info">
                <b>+</b> Zutat Filter
              </button>
            </div>
            <div>
              <label>Max. Zeit:</label>
              <input name="time" v-model="ing_time" type="time" placeholder="Wie viel Zeit hast du?" class="form-control border border-primary"/>
              <small class="text-danger form-text">Vollständig ausfüllen!</small>
            </div>
          </div>
          <div class="d-flex flex-wrap my-2">
            <input name="ingredient[]" v-for="i in ing_whitelist.length" v-bind:key="i"  v-model="ing_whitelist[i-1]" type="search" placeholder="Zutat" class="form-control border border-info my-2"/>
          </div>
          <button @click="submit" type="button" class="btn btn-success w-100">
            <b>&#x1F50D; Suchen</b>
          </button>
        </form>
</template>

<script>
export default {
  name: 'AedvancedSearch',
  data(){
    return {
        ing_name: "",
        ing_time: "",
        ing_whitelist: [],
        foodtypes: window.foodtypes,
        foodtype: "",
        foodtypedisplay:"Auswählen"
    };
  },
  props: {

  },
  methods:{
    add_whitelist_input(){
        this.ing_whitelist.push("");
        //console.log(this.ing_whitelist);
    },
    submit(){
      const formData = new FormData(this.$refs.form);
      let filter = {
        title: formData.get("title"),
        dishtype: formData.get("dishtype"),
        time: formData.get("time"),
        ingredients: formData.getAll("ingredient")
      };
      this.$emit("filter-update", filter);
    },
    setFoodType(para){
        this.foodtype = para.target.innerHTML;
        this.foodtypedisplay = this.foodtype;
    }
  }
}
</script>