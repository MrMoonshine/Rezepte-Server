<template>
  <dialog ref="dialog" class="p-4 overflow-auto shadow-lg rounded bg-light">
    <div class="d-flex justify-content-between d-print none">
      <h2 class="text-primary flex-grow-1">Konfiguration</h2>
      <button
        @click="close"
        type="button"
        class="btn-close mt-2"
        aria-label="Close"
      ></button>
    </div>
    <LogList :logs="logs"></LogList>
    <div class="d-flex">
      <div class="d-block">
        <fieldset class="rounded border border-primary p-3 m-2">
          <legend>Backup</legend>
          <div class="mb-3">
            <label for="takeSnapshotButton" class="form-label"
              >Download Snapshot</label
            >
            <br />
            <a
              href="assets/rezepte.sqlite3"
              :download="backupName"
              class="btn btn-primary w-100"
              id="takeSnapshotButton"
              aria-describedby="takeSnapshotButtonHelp"
            >
              Neuer Snapshot
              <span
                class="text-primary bg-light rounded-circle font-weight-bold"
                style="padding: 0px 0.4rem"
                >&darr;</span
              >
            </a>
            <div id="takeSnapshotButtonHelp" class="form-text">
              Erstellt einen Snapshot, der heruntergeladen werden kann. (Ohne
              Rezeptbilder)
            </div>
          </div>
          <form
            @submit="this.applyBackup($event)"
            :action="dbscript"
            method="POST"
            enctype="multipart/form-data"
          >
            <div class="mb-3">
              <label for="formFile" class="form-label"
                >Snapshot Hochladen</label
              >
              <input
                class="form-control"
                type="file"
                id="formFile"
                name="snapshot"
                accept=".sqlite3,.db"
              />
              <div class="form-text">
                Aktuelle daten weden durch den hochgeladenen Snapshot ersetzt!
              </div>
            </div>
            <input
              type="submit"
              class="btn btn-warning w-100"
              value="Snapshot Einspielen"
            />
          </form>
        </fieldset>
        <fieldset class="rounded border border-primary p-3 m-2">
          <legend>Legacy JSON Import</legend>
          <p>Importiert alle alten JSON Files für Rezepte</p>
          <button @click="loadLegacy" class="w-100 btn btn-warning">
            Import JSON
          </button>
        </fieldset>
        <ConfigVolume :action="dbscript"></ConfigVolume>
      </div>
      <fieldset class="rounded border border-primary p-3 m-2">
        <DialogConfigminiform
          title="Einheit"
          table="units"
          @configdb-update="this.fetchSimpleTable('units', $event)"
          :action="dbscript"
          description="Einheiten wie z.B Gramm, Liter etc..."
        ></DialogConfigminiform>
        <DialogConfigminiform
          v-for="unit in simpleTables['units']"
          v-bind:key="unit"
          table="units"
          @configdb-update="this.fetchSimpleTable('units', $event)"
          :value="unit.name"
          :dbid="unit.id"
          :action="dbscript"
          deleteform
        ></DialogConfigminiform>
      </fieldset>
      <fieldset class="rounded border border-primary p-3 m-2">
        <DialogConfigminiform
          title="Speiseart"
          table="dishtypes"
          @configdb-update="this.fetchSimpleTable('dishtypes', $event)"
          :action="dbscript"
          description="Speisearten wie z.B Vorspeise, Hauptspeise etc..."
        ></DialogConfigminiform>
        <DialogConfigminiform
          v-for="dishtype in simpleTables['dishtypes']"
          v-bind:key="dishtype"
          table="dishtypes"
          @configdb-update="this.fetchSimpleTable('dishtypes', $event)"
          :value="dishtype.name"
          :dbid="dishtype.id"
          :action="dbscript"
          deleteform
        ></DialogConfigminiform>
      </fieldset>
      <fieldset class="rounded border border-primary p-3 m-2">
        <DialogConfigminiform
          title="Allergen"
          table="allergenes"
          @configdb-update="this.fetchSimpleTable('allergenes', $event)"
          :action="dbscript"
          description="Allergene wie z.B Glutenfrei, Vegan, etc..."
        ></DialogConfigminiform>
        <DialogConfigminiform
          v-for="allergene in simpleTables['allergenes']"
          v-bind:key="allergene"
          table="allergenes"
          @configdb-update="this.fetchSimpleTable('allergenes', $event)"
          :value="allergene.name"
          :dbid="allergene.id"
          :action="dbscript"
          deleteform
        ></DialogConfigminiform>
      </fieldset>
    </div>
  </dialog>
</template>
<script>
import DialogConfigminiform from "./DialogConfigminiform.vue";
import LogList from "./LogList.vue";
import ConfigVolume from "./ConfigVolume.vue";

export default {
  name: "DialogConfig",
  data() {
    return {
      simpleTables: [],
      dbscript: "rezepte/dist/database.php",
      dburl:
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/rezepte/",
      backupName: "rezepte.sqlite3",
      logs: [],
    };
  },
  components: {
    DialogConfigminiform,
    LogList,
    ConfigVolume
  },
  mounted() {
    this.fetchSimpleTable("allergenes");
    this.fetchSimpleTable("units");
    this.fetchSimpleTable("dishtypes");

    // Set name for download file
    let date = new Date();
    this.backupName = "rezepte-";
    this.backupName += date.toISOString();
    this.backupName += ".sqlite3";
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
    fetchSimpleTable(table, data = null) {
      if (data) {
        // use passed data by insert and select php-script
        this.simpleTables[table] = data;
        return;
      }
      const req = new XMLHttpRequest();
      const url = new URL(this.dburl + this.dbscript);
      url.searchParams.append("select", table);
      //console.log(url);
      req.addEventListener("load", () => {
        //console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        if (jobj) {
          this.simpleTables[table] = jobj.data;
          //console.log(this.simpleTables[table]);
          this.logs = jobj.logs;
        }
      });
      req.open("GET", url.href);
      req.send();
    },
    applyBackup(event) {
      //console.log(event);
      const formData = new FormData(event.target);

      const req = new XMLHttpRequest();
      const url = new URL(this.dburl + this.dbscript);
      req.open("POST", url.href);
      req.addEventListener("load", () => {
        //console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        //console.log(jobj);
        if (jobj) {
          this.logs = jobj.logs;
          if (this.logs.length == 0) {
            jobj.logs.push({
              severity: "Info",
              msg: "Upload OK, Datenbank wurde überschrieben. Reload empfohlen.",
            });
          }
        }
      });
      req.send(formData);
      event.preventDefault();
    },
    loadLegacy() {
      const req = new XMLHttpRequest();
      const url = new URL(this.dburl + this.dbscript);
      url.searchParams.append("import", "json");
      //console.log(url);
      req.addEventListener("load", () => {
        //console.log(req.responseText);
        let jobj = JSON.parse(req.responseText);
        if (jobj) {
          this.logs = jobj.logs;
        }
      });
      req.open("GET", url.href);
      req.send();
    },
  },
};
</script>
