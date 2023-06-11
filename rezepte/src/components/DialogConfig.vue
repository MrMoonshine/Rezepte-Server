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
          <DialogConfigminiform title="Allergen" table="allergenes" @allergenes-update="fetchAllergeneTable"
            :action="dbscript" description="Allergene wie z.B Glutenfrei, Vegan, etc..."></DialogConfigminiform>
          <DialogConfigminiform v-for="allergene in allergenes" v-bind:key="allergene" table="allergenes"
            @allergenes-update="fetchAllergeneTable" :value="allergene.name" :dbid="allergene.id" :action="dbscript"
            deleteform></DialogConfigminiform>
        </fieldset>
      </div>
      <fieldset class="rounded border border-primary p-3 m-2">
        <DialogConfigminiform title="Einheit" table="units" @units-update="test" :action="dbscript"
          description="Einheiten wie z.B Gramm, Liter etc..."></DialogConfigminiform>
        <DialogConfigminiform v-for="unit in units" v-bind:key="unit" table="units" @units-update="fetchUnitsTable"
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
      allergenes: [],
      units: [],
      dbscript: "rezepte/dist/database.php",
      dburl: window.location.protocol + "//" + window.location.hostname + "/rezepte/",
    };
  },
  components: {
    DialogConfigminiform
  },
  mounted() {
    this.fetchAllergeneTable();
    this.fetchUnitsTable();
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
    fetchAllergeneTable() {
      const req = new XMLHttpRequest();
      const url = new URL(this.dburl + this.dbscript);
      url.searchParams.append("allergenes", "");
      console.log(url);
      req.addEventListener("load", () => {
        let jobj = JSON.parse(req.responseText);
        if (jobj) {
          this.allergenes = jobj.data;
          console.log(this.allergenes);
        }
      });
      req.open("GET", url.href);
      req.send();
    },
    fetchUnitsTable() {
      const req = new XMLHttpRequest();
      const url = new URL(this.dburl + this.dbscript);
      url.searchParams.append("units", "");
      console.log(url);
      req.addEventListener("load", () => {
        let jobj = JSON.parse(req.responseText);
        if (jobj) {
          this.units = jobj.data;
          console.log(this.units);
        }
      });
      req.open("GET", url.href);
      req.send();
    }
  }
}
</script>
