<template>
  <div v-if="show" class="d-flex justify-content-between pb-2 border-bottom">
    <div class="d-block">
      <label class="form-label">Zutat</label>
      <input
        v-model="name"
        class="form-control border-primary"
        type="text"
        name="zutat[]"
        required
      />
    </div>
    <div class="d-block">
      <label class="form-label">Menge</label>
      <input
        v-model="amount"
        class="form-control border-primary"
        type="number"
        name="menge[]"
        min="0"
        step="0.1"
      />
    </div>
    <div class="d-block">
      <label class="form-label">Einheit</label><br />
      <DropdownSelect ref="inputUnit" name="einheit[]" :options="units"></DropdownSelect>
    </div>
    <div class="d-block">
      <label class="form-label">Entfernen</label><br />
      <input
        @click="show = false"
        class="btn btn-danger"
        type="button"
        value="X"
      />
    </div>
  </div>
</template>

<script>
import DropdownSelect from "./DropdownSelect.vue";

const UNIT_DEFAULT = { name: "g", id: 1 };

export default {
  name: "RecipeNewingredient",
  components: {
    DropdownSelect
  },
  data() {
    return {
      show: true,
      name: "",
      amount: 0,
      //unit: { name: "g", id: 1 },
    };
  },
  props: {
    ingredient: {
      name: String,
      amount: Number,
      unit: String,
    },
    units: Array,
  },
  methods: {

  },
  mounted() {
    // Values, if defined
    if (this.ingredient.name) {
      this.name = this.ingredient.name;
    }

    if (this.ingredient.amount) {
      this.amount = this.ingredient.amount;
    }

    // Default to grams for now
    this.$refs.inputUnit.setItem(UNIT_DEFAULT);

    if (this.ingredient.unit) {
      //cycle through all units, since only names are passed
      for(const u of this.units){
        if(u.name == this.ingredient.unit){
            this.$refs.inputUnit.setItem(u);
            break;
        }
      }
    }
  },
};
</script>
