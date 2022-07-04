<template>
    <div class="card card-body">
          <h2>Fortgeschrittene Suche</h2>
          <p>Jedes Feld was du ausfüllst ist für die Suche relevant. Alle anderen sind egal</p>
          <div class="d-flex flex-wrap justify-content-between gap-2 pb-3 border-bottom">
            <div class="flex-grow-1">
              <label>Name:</label>
              <input v-model="ing_name" type="search" placeholder="Name" class="form-control border border-primary"/>
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
              <input v-model="ing_time" type="time" placeholder="Wie viel Zeit hast du?" class="form-control border border-primary"/>
              <small class="text-danger form-text">Vollständig ausfüllen!</small>
            </div>
          </div>
          <div class="d-flex flex-wrap my-2">
            <input v-for="i in ing_whitelist.length" v-bind:key="i"  v-model="ing_whitelist[i-1]" type="search" placeholder="Zutat" class="form-control border border-info my-2"/>
          </div>
          <button @click="emit_form" type="button" class="btn btn-success">
            <b>&#x1F50D; Suchen</b>
          </button>
        </div>
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
    emit_form(){
      var criteria = {
        name: this.ing_name,
        foodtype: this.foodtype,
        time: this.ing_time,
        whitelist: this.ing_whitelist,
        blacklist: []
      };
      this.$emit("advanced-search", criteria);
    },
    setFoodType(para){
        this.foodtype = para.target.innerHTML;
        this.foodtypedisplay = this.foodtype;
    }
  }
}
</script>