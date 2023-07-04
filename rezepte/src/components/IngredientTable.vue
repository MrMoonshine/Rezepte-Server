<template>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Zutat</th>
                <th scope="col">Menge</th>
                <th v-if="conversionCount > 0" scope="col">Volumenmaß</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="ingredient in ingredients" v-bind:key="ingredient">
                <td>{{ ingredient.name }}</td>
                <!--<td>{{ this.round2(ingredient.amount * calcfactor)}} {{ ingredient.unit }}</td>-->
                <td>{{this.defaultMeassure(ingredient.amount * calcfactor, ingredient)}}</td>
                <td v-if="conversionCount > 0">{{ this.volumeMeassure(ingredient.amount * calcfactor, ingredient) }}</td>
            </tr>
        </tbody>
    </table>
    <div class="d-flex justify-content-between d-print-none">
        <label class="fw-bold">Portionen:</label>
        <div class="btn-group">
            <button @click="alterAmount(-1)" class="btn btn-primary"><b>-</b></button>
            <button class="disabled btn btn-light border rounded-0 border-primary">{{ calcamount }}</button>
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
        calcfactor: 0,
        conversionCount: 0  // # of possible conversions to volumes
    };
  },
  props: {
    ingredients: [{
        name: String,
        amount: Number,
        unit: String
    }],
    amount: Number,         // The amount given in the recipe itself
    densities: []             // Conversion table vor volume meassures
  },
  methods:{
    alterAmount(diff){
        if(this.calcamount - (-diff) > 0){
            this.calcamount -= -diff;
            this.calcfactor = this.calcamount / this.amount;
        }
    },
    // round to 2 digits after comma.
    round2(value){
        return Math.round(100 * value)/100;
    },
    //  amount_i:   calculated amount in mass
    //  ing_obj:    js object of ingredient
    volumeMeassure(amount_i, ing_obj){
        let unit = ing_obj.unit;
        // If already in liter, check if it can be converted backwards
        if(unit == "l" || unit == "ml"){
            let hasConversion = false;
            for(const conversion of this.densities){
                if(conversion.name == ing_obj.name){
                    hasConversion = true;
                }
            }

            if(!hasConversion){
                return "";
            }

            if(unit == "l"){
                return this.round2(amount_i) + " " + unit;
            }else{
                return Math.round(amount_i) + " " + unit;
            }            
        }

        if(unit != "g" && unit != "kg"){
            return "";
        }
        // mass = volume * density
        //  m = V * p
        let amount = amount_i * 1000; // in ml
        let hasConversion = false;
        // Multiply by 1000 for kilo to get gramms
        if(unit == "kg"){
            amount *= 1000;
        }
        // Cycle through options
        for(const conversion of this.densities){
            if(conversion.name == ing_obj.name){
                amount /= conversion.density;
                hasConversion = true;
            }
        }
        // Return nothing if no conversion is possible
        if(!hasConversion){
            return "";
        }
        // Submit a rounded result in ml or l
        if(amount < 1000){
            return Math.round(amount) + " ml";
        }else{
            return this.round2(amount/1000) + " l";
        }
    },
    defaultMeassure(amount_i, ing_obj){
        let unit = ing_obj.unit;
        if((unit != "l" && unit != "ml") || this.densities.length < 1){
            return this.round2(amount_i) + " " + unit;
        }
        let amount = amount_i; // in liters
        // Multiply by 1000 for kilo to get gramms
        if(unit == "ml"){
            amount /= 1000;
        }

        for(const conversion of this.densities){
            if(conversion.name == ing_obj.name){
                amount *= conversion.density;
            }
        }

        // Submit a rounded result in g or kg
        if(amount < 1000){
            return Math.round(amount) + " g";
        }else{
            return this.round2(amount/1000) + " kg";
        }
    }
  },
  created(){
    // Reset amount
    this.calcamount = this.amount;
    this.calcfactor = 1.0;

    // Count conversions to determine if column is necessary
    this.conversionCount = 0;
    for(const ingredient of this.ingredients){
        if(ingredient.unit != "g" && ingredient.unit != "kg" && ingredient.unit != "l" && ingredient.unit != "ml"){
            continue;
        }

        for(const conversion of this.densities){
            if(conversion.name != ingredient.name){
                continue;
            }
            this.conversionCount += 1;
        }
    }
  }
}
</script>