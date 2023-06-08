<template>
    <dialog ref="dialog" class="p-4 overflow-auto shadow-lg rounded bg-light">
      <div class="d-flex justify-content-between d-print none">
          <h2 class="text-primary flex-grow-1">Konfiguration</h2>
          <button @click="close" type="button" class="btn-close mt-2" aria-label="Close" />
      </div>
      <DialogConfigminiform 
        title="Allergen"
        table="allergenes"
        @allergenes-update="test"
        :action="dbscript"
        description="Allergene wie z.B Glutenfrei, Vegan, etc..."
      ></DialogConfigminiform>
      <DialogConfigminiform
        v-for="allergene in allergenes"
        v-bind:key="allergene"
        table="allergenes"
        @allergenes-update="test"
        :value="allergene.name"
        :dbid="allergene.id"
        :action="dbscript"
        deleteform
      ></DialogConfigminiform>
    </dialog>
</template>
<script>
import DialogConfigminiform from './DialogConfigminiform.vue'
export default {
  name: 'DialogConfig',
  data(){
    return {
      allergenes: [],
      dbscript: "rezepte/dist/database.php",
      dburl: window.location.protocol + "//" + window.location.hostname + "/rezepte/"
    };
  },
  components:{
    DialogConfigminiform
  },
  mounted() {
    this.fetchAllergeneTable();
  },
  methods:{
    showDialog(){
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.showModal();
    },
    close(){
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.close();
    },
    fetchAllergeneTable(){
      const req = new XMLHttpRequest();
      const url = new URL(this.dburl + this.dbscript);
      url.searchParams.append("allergenes", "");
      console.log(url);
      req.addEventListener("load", () => {
        let jobj = JSON.parse(req.responseText);
        if(jobj){
          this.allergenes = jobj.data;
          console.log(this.allergenes);
        }
      });
      req.open("GET", url.href);
      req.send();
    },
    test(){
      console.log("Received event!");
      this.fetchAllergeneTable();
    }
  }
}
</script>