<template>
    <form ref="form" class="p-2 my-2 border rounded">
          <h2>Suche</h2>
          <p>Jedes Feld was du ausfüllst ist für die Suche relevant. Alle anderen sind egal</p>
          <div class="d-block pb-1">
            <label>Name:</label>
            <div class="input-group">
              <input name="title" @keypress="submitOnEnter" type="search" placeholder="Name" class="form-control border border-primary"/>
              <button class="input-group-text btn btn-primary dropdown-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#adv-search-form" aria-expanded="false" aria-controls="adv-search-form">Advanced</button>
            </div>
          </div>
          <div class="collapse" id="adv-search-form">
            <div class="card card-body my-2 p-2">
              <div class="d-flex justify-content-between gap-3">
                <div class="d-block flex-grow-1">
                  <label for="ingfil" class="form-label">Zutatenfilter</label>
                  <div v-for="i in ing_whitelist.length" v-bind:key="i" class="input-group my-1" id="ingfil">
                    <input name="ingredients[]" v-model="ing_whitelist[i - 1]" type="search" placeholder="Zutat" class="form-control border border-info" autocomplete="off"/>
                    <button @click="this.delete_ing_wl(i - 1)" type="button" class="btn btn-danger"><b>-</b></button>
                  </div>
                  <button @click="add_whitelist_input" type="button" class="btn btn-info my-1 w-100">
                    <b>+</b> Zutat Filter
                  </button>
                </div>
                <div class="d-block filter-col-fixed">
                  <DropdownSelect name="dishtype" :options="metadata['dishtypes']" unsetstr="- ungefiltert -"></DropdownSelect>
                  <div class="d-block my-1">
                    <label for="allergenfil" class="form-label">Allergene</label>
                    <div id="allergenfil" v-for="allergen in metadata['allergenes']" v-bind:key="allergen" class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" :value="allergen.id" name="allergenes[]" :id="'allergen' + allergen.name + 'switch'">
                      <label class="form-check-label" :for="'allergen' + allergen + 'switch'">{{ allergen.name }}</label>
                    </div>
                  </div>
                  <div class="d-block my-1">
                    <label>Max. Zeit:</label>
                    <input name="time" type="time" placeholder="Wie viel Zeit hast du?" class="form-control border border-primary"/>
                    <small class="text-danger form-text">Zeit vollständig ausfüllen!</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button @click="submit('filter-random')" type="button" class="btn btn-secondary w-100">
              <b> &#x1F3B2; Züfällig</b>
            </button>
            <button @click="submit('filter-update')" type="button" class="btn btn-primary w-100">
              <b>&#x1F50D; Suchen</b>
            </button>
          </div>
        </form>
</template>

<script>
import DropdownSelect from './DropdownSelect.vue'

export default {
  name: 'AedvancedSearch',
  components:{
    DropdownSelect
  },
  data(){
    return {
        ing_whitelist: [],
        dishtype: {name: 'Auswählen', id:-1},
    };
  },
  props: {
    metadata: Array
  },
  methods:{
    add_whitelist_input(){
        this.ing_whitelist.push("");
    },
    delete_ing_wl(i){
      // delete one element at index
      this.ing_whitelist.splice(i, 1);
    },
    // helper function for keypress event on the main search bar
    // Is does the same as the submit function below
    submitOnEnter(event){
      if(event.key === "Enter"){
        this.submit("filter-update");
      }
    },
    // parameter event is the event being sent out by vue
    submit(event){
      const formData = new FormData(this.$refs.form);
      // Convert time into minutes first
      let timestr = formData.get("time");
      let time = 0;
      if(timestr.length > 0){
        let a = timestr.split(":");
        if(a.length > 1){
          time = parseInt(a[0]) * 60 + parseInt(a[1]);
        }
      }
      //console.log(formData);
      let filter = {
        title: formData.get("title"),
        dishtype: formData.get("dishtype"),
        time: time,
        allergenes: formData.getAll("allergenes[]"),
        ingredients: formData.getAll("ingredients[]")
      };
      //console.log(filter);
      this.$emit(event, filter);
    }
  }
}
</script>
<style>
.filter-col-fixed{
  width: 12em;
}

.filter-col-fixed .form-control{
  width: unset;
}

</style>