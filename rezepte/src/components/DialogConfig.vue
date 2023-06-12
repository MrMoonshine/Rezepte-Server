<template>
  <dialog ref="dialog" class="p-4 overflow-auto shadow-lg rounded bg-light">
    <div class="d-flex justify-content-between d-print none">
      <h2 class="text-primary flex-grow-1">Konfiguration</h2>
      <button @click="close" type="button" class="btn-close mt-2" aria-label="Close"></button>
    </div>
    <div class="d-flex">
      <div class="d-block">
        <fieldset class="rounded border border-primary p-3 m-2">
          <legend>Backup</legend>
          <div class="mb-3">
            <label for="takeSnapshotButton" class="form-label">Download Snapshot</label>
            <br>
            <button class="btn btn-primary w-100" id="takeSnapshotButton" aria-describedby="takeSnapshotButtonHelp">
              Neuer Snapshot 
              <span class="text-primary bg-light rounded-circle font-weight-bold" style="padding: 0px 0.4rem;">&darr;</span>
            </button>
            <div id="takeSnapshotButtonHelp" class="form-text">Erstellt einen Snapshot, der heruntergeladen werden kann</div>
          </div>
          <form>
            <div class="mb-3">
              <label for="formFile" class="form-label">Stapshot Hochladen</label>
              <input class="form-control" type="file" id="formFile">
              <div class="form-text">Aktuelle daten weden durch den hochgeladenen Snapshot ersetzt!</div>
            </div>
            <input type="submit" class="btn btn-warning w-100" value="Snapshot Einspielen"/>
          </form>         
        </fieldset>
        <fieldset class="rounded border border-primary p-3 m-2">
          <DialogConfigminiform title="Allergen" table="allergenes" @configdb-update="this.fetchSimpleTable('allergenes')"
            :action="dbscript" description="Allergene wie z.B Glutenfrei, Vegan, etc..."></DialogConfigminiform>
          <DialogConfigminiform v-for="allergene in simpleTables['allergenes']" v-bind:key="allergene" table="allergenes"
            @configdb-update="this.fetchSimpleTable('allergenes')" :value="allergene.name" :dbid="allergene.id" :action="dbscript"
            deleteform></DialogConfigminiform>
        </fieldset>
      </div>
      <fieldset class="rounded border border-primary p-3 m-2">
        <DialogConfigminiform title="Einheit" table="units" @configdb-update="this.fetchSimpleTable('units')" :action="dbscript"
          description="Einheiten wie z.B Gramm, Liter etc..."></DialogConfigminiform>
        <DialogConfigminiform v-for="unit in simpleTables['units']" v-bind:key="unit" table="units" @configdb-update="this.fetchSimpleTable('units')"
          :value="unit.name" :dbid="unit.id" :action="dbscript" deleteform></DialogConfigminiform>
      </fieldset>
    </div>
  </dialog>
</template>
<script>
import DialogConfigminiform from './DialogConfigminiform.vue'
export default {
  name: 'DialogConfig',
  data() {
    return {
      simpleTables: [],
      dbscript: "rezepte/dist/database.php",
      dburl: window.location.protocol + "//" + window.location.hostname + "/rezepte/",
    };
  },
  components: {
    DialogConfigminiform
  },
  mounted() {
    this.fetchSimpleTable('allergenes');
    this.fetchSimpleTable('units');
  },
  methods: {
    showDialog() {
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.showModal();
    },
    close() {
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.close();
    },
    fetchSimpleTable(table) {
      const req = new XMLHttpRequest();
      const url = new URL(this.dburl + this.dbscript);
      url.searchParams.append("select", table);
      console.log(url);
      req.addEventListener("load", () => {
        //console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        if (jobj) {
          this.simpleTables[table] = jobj.data;
          console.log(this.simpleTables[table]);
        }
      });
      req.open("GET", url.href);
      req.send();
    }
  }
}
</script>
