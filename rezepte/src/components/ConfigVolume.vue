<template>
  <fieldset class="rounded border border-primary p-3 m-2">
    <legend>Volumenmaße</legend>
    <LogList :logs="logs"></LogList>
    <form
      ref="addForm"
      class="d-flex gap-2 mb-3"
      method="POST"
      :action="action"
    >
      <input name="insert" class="d-none" value="ingredient_density">
      <input
        name="ingredient"
        placeholder="Zutat"
        type="text"
        class="form-control"
      />
      <div class="input-group">
        <input
          name="density"
          placeholder="Dichte in g/l"
          type="text"
          class="form-control"
        />
        <div class="input-group-text">g/l</div>
      </div>
      <button @click="this.submit(true)" type="button" class="btn btn-success">
        <b>+</b>
      </button>
    </form>
  </fieldset>
</template>

<script>
import LogList from "./LogList.vue";

export default {
  name: "ConfigVolume",
  components: {
    LogList,
  },
  data() {
    return {
        logs: []
    };
  },
  props: {
    action: String,
    metadata: Array,
  },
  methods: {
    reqHandler(req) {
        if (req.status != 200) {
          this.logs.push({
            severity: "Critical",
            msg: "XHR Error: " + req.status + " " + req.statusText,
          });
          return;
        }
        /*
            Write all logs into a array. it gets further processed by VUE
        */
        let jobj = JSON.parse(req.responseText);
        this.logs = [];

        if(jobj){
            jobj.logs.forEach(log => {
              this.logs.push(log);
              this.showerr = true;
            });
            console.log(jobj);
        }else{
            this.logs.push({
              severity: "Critical",
              msg: "JSON parse fehler!"
            });
            console.log(req.responseText);
            this.showerr = true;
        }
    },
    submit() {
      const url = new URL(
        window.location.protocol +
          "//" +
          window.location.hostname +
          "/rezepte/" +
          this.action
      );
      const formData = new FormData(this.$refs.addForm);
      const req = new XMLHttpRequest();

      // Always select the table afterwards, to not make an additional request
      url.searchParams.append("select", "ingredient_density");

      req.addEventListener("load", () => {
        this.reqHandler(req);
      });

      req.open("POST", url.href);
      req.send(formData);
    },
  },
};
</script>
