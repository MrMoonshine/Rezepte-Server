<template>
    <div class="fixed-bottom">
        <ul class="pagination justify-content-center">
            <li class="page-item">
            <button @click="this.emitPage(current)" class="page-link" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </button>
            </li>
            <li v-for="page in parseInt(total)" v-bind:key="page" :class="this.pageLinkClass(page)">
              <button @click="this.emitPage(page)" class="page-link">{{ page }}</button>
            </li>
            <li class="page-item">
            <button @click="this.emitPage(current + 2)" class="page-link" href="#" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </button>
            </li>
        </ul>
    </div>
</template>
<script>
export default {
  name: 'PaginationSelection',
  data(){
    return {};
  },
  props: {
    total: Number,
    current: Number
  },
  methods:{
    pageLinkClass(index){
      const base = "page-item";
      // the +1 is because the DB calculation starts with 0
      if(index == parseInt(this.current) + 1){
        return base + " active";
      }
      return base;
    },
    emitPage(page){
      let pg = parseInt(page);
      // out of bounds handling
      if(pg < 1){
        pg = 1;
      }else if(pg > this.total){
        pg = this.total;
      }
      // DB starts counting with 0
      pg--;
      // emit
      this.$emit("page-update", pg);
    }
  }
}
</script>