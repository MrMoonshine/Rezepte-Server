<template>
    <div>
        <ul class="pagination justify-content-center">
            <li class="page-item">
            <a class="page-link" href="#" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
            </li>
            <li v-for="page in parseInt(total)" v-bind:key="page" :class="this.pageLinkClass(page)"><a class="page-link" href="#">{{ page }}</a></li>
            <li class="page-item">
            <a class="page-link" href="#" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
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
      if(index == parseInt(this.current)){
        return base + " active";
      }
      return base;
    }
  }
}
</script>