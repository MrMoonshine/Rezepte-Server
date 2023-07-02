<template>
  <fieldset class="rounded border border-primary p-3 m-2">
    <legend>Volumenmaße</legend>
    <LogList :logs="logs"></LogList>
    <form
      @submit="submit"
      ref="addForm"
      class="d-flex gap-2 mb-3"
      method="POST"
      :action="action"
    >
      <input name="insert" class="d-none" value="ingredient_density" />
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
      <button type="submit" class="btn btn-success">
        <b>+</b>
      </button>
    </form>
    <form @submit="submit"
      class="d-flex gap-2 mb-3"
      method="POST"
      :action="action"
      v-for="density in densities"
      v-bind:key="density"
    >
      <input name="delete" class="d-none" value="ingredient_density" />
      <input name="ingredient" class="d-none" :value="density.id" />
      <div class="input-group">
        <input
          type="text"
          class="form-control"
          :value="density.name ?? 'Error'"
          readonly
        />
        <input
          type="text"
          class="form-control"
          :value="(density.density ?? 'Error') + ' g/l'"
          readonly
        />
      </div>
      <button type="submit" class="btn btn-danger">
        <b>-</b>
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
      logs: [],
      densities: [],
    };
  },
  props: {
    action: String,
    metadata: Array,
  },
  mounted() {
    // no form to submit & readonly
    this.submit(null);
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

      if (jobj) {
        this.logs = jobj.logs ?? [];
        this.densities = jobj.data ?? [];
        console.log(jobj);
      } else {
        this.logs.push({
          severity: "Critical",
          msg: "JSON parse fehler!",
        });
        console.log(req.responseText);
        this.showerr = true;
      }
    },
    submit(forme) {
      const url = new URL(
        window.location.protocol +
          "//" +
          window.location.hostname +
          "/rezepte/" +
          this.action
      );
      const req = new XMLHttpRequest();

      // Always select the table afterwards, to not make an additional request
      url.searchParams.append("select", "ingredient_density");

      req.addEventListener("load", () => {
        this.reqHandler(req);
      });
      
      req.open("POST", url.href);
      if (forme) {
        const formData = new FormData(forme.target);
        req.send(formData);
        forme.preventDefault();
      } else {
        req.send();
      }
    },
  },
};
</script>
