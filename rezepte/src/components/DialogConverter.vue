<template>
  <dialog ref="dialog" class="p-4 overflow-auto shadow-lg rounded bg-light">
    <div class="d-flex justify-content-between d-print none">
      <h2 class="text-primary flex-grow-1">Umrechner</h2>
      <button @click="close" type="button" class="btn-close mt-2" aria-label="Close"></button>
    </div>
    <div>
      <p>
        Wähle eine zutat aus und Rechne zwischen Masse und Volumen hin und her.
      </p>
      <div class="d-flex gap-2 mb-3">
        <DropdownSelect
          ref="dropdown"
          @item-set="setIngredient"
          name="ingredient"
          :options="data"
        ></DropdownSelect>
        <div class="input-group-text">Dichte: {{ density }} g/l</div>
      </div>
      <div class="row gap-2">
        <fieldset class="col rounded border border-primary">
          <legend>Masse</legend>
          <div class="input-group mb-3">
            <input @input="updateMass($event.target.value)" :value="round2(mass)" type="number" class="form-control" min="0" step="1" />
            <div class="input-group-text">g</div>
          </div>
          <div class="input-group mb-3">
            <input @input="updateMass($event.target.value * 1000)" :value="round2(mass/1000)" type="number" class="form-control" min="0" step="0.1" />
            <div class="input-group-text">kg</div>
          </div>
        </fieldset>
        <fieldset class="col rounded border border-primary">
          <legend>Volumen</legend>
          <div class="input-group mb-3">
            <input @input="updateVolume($event.target.value / 1000)" :value="Math.round(volume * 1000)" type="number" class="form-control" min="0" step="1" />
            <div class="input-group-text">ml</div>
          </div>
          <div class="input-group mb-3">
            <input @input="updateVolume($event.target.value)" :value="round2(volume)" type="number" class="form-control" min="0" step="0.1" />
            <div class="input-group-text">l</div>
          </div>
        </fieldset>
      </div>
    </div>
  </dialog>
</template>

<script>
import DropdownSelect from "./DropdownSelect.vue";

export default {
  name: "DialogConverter",
  components: {
    DropdownSelect,
  },
  data() {
    return {
      density: 0, // density in g/l
      mass: 1,    // in grams
      volume: 1   // in liters
    };
  },
  props: {
    data: [],
  },
  methods: {
    showDialog() {
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.showModal();
      if (this.data.length < 1) {
        return;
      }
      this.$refs.dropdown.setItem(this.data[0]);
    },
    close() {
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.close();
    },
    setIngredient(id) {
      this.density = id.density ?? -1;
      this.updateMass(this.mass);
    },
    updateMass(m){
      this.mass = m;
      this.volume = this.mass / this.density;
    },
    updateVolume(v){
      this.volume = v;
      this.mass = this.volume * this.density;
    },
    round2(value){
        return Math.round(100 * value)/100;
    }
  },
};
</script>
