<template>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Zutat</th>
                <th scope="col">Menge</th>
                <th scope="col">Einheit</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="ingredient in ingredients" v-bind:key="ingredient">
                <td>{{ ingredient.name }}</td>
                <td>{{ ingredient.amount * calcfactor}}</td>
                <td>{{ ingredient.unit }}</td>
            </tr>
        </tbody>
    </table>
    <div class="d-flex justify-content-between">
        <label class="fw-bold">Portionen:</label>
        <div class="btn-group">
            <button @click="alterAmount(-1)" class="btn btn-primary"><b>-</b></button>
            <input :value="calcamount" type="text" class="disabled btn border rounded-0 border-primary"/>
            <button @click="alterAmount(1)" class="btn btn-primary"><b>+</b></button>
        </div>
    </div>
</template>

<script>

export default {
  name: 'IngredientTable',
  data(){
    return {
        calcamount: 0,
        calcfactor: 0
    };
  },
  props: {
    ingredients: [{
        name: String,
        amount: Number,
        unit: String
    }],
    // The amount given in the recipe itself
    amount: Number
  },
  methods:{
    alterAmount(diff){
        if(this.calcamount - (-diff) > 0){
            this.calcamount -= -diff;
            this.calcfactor = Math.round(100 * this.calcamount / this.amount)/100;
        }
    }
  },
  created(){
    // Reset amount
    this.calcamount = this.amount;
    this.calcfactor = 1.0;
  }
}
</script>