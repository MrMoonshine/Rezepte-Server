<template>
<div class="dropdown">
  <input :value="selected.id ?? -1" class="d-none" type="number" :name="name" readonly/>
  <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    {{ selected.name ?? "Error"}}
  </button>
  <ul class="dropdown-menu">
    <li v-if="unsetstr">
        <button @click="this.setItem({name: 'Auswählen', id:-1})" class="dropdown-item" type="button">{{unsetstr}}</button>
    </li>
    <li v-for="item in options" v-bind:key="item">
        <button @click="this.setItem(item)" class="dropdown-item" type="button">{{ item.name ?? "Error"}}</button>
    </li>
  </ul>
</div>
</template>

<script>
export default {
  name: 'DropdownSelect',
  data(){
    return {
        selected: {name: 'Auswählen', id:-1}
    };
  },
  props: {
    name: String,
    options: Array,     // Expects an array of objects like: {name: "", id:0}
    unsetstr: String,   // Option name for unselected,
    initial: Object     // Initial value
  },
  methods:{
    setItem(item){
        this.selected = item;
        this.$emit("item-set", item);
    }
  },
  mounted(){
    if(!this.initial){
      return;
    }

    this.selected = this.initial
  }
}
</script>