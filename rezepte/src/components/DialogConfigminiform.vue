<template>
    <form class="mb-3" method="POST" :action="action">
        <label v-if="title" for="table + 'Input'" class="form-label">{{title}}</label>
        <div class="input-group">
          <input ref="input" type="text" class="form-control" :id="table + 'Input'" :name="table" :value="value" :aria-describedby="table + 'Help'" required>
          <input v-if="deleteform" type="text" name="delete" value="1" class="d-none" />
          <input ref="dbid" type="number" name="delete" :value="dbid" class="d-none" />

          <button v-if="deleteform" @click="submit" type="button" class="btn btn-danger"><b>-</b></button>
          <button v-else @click="submit" type="button" class="btn btn-success"><b>+</b></button>
        </div>
        <div v-if="description" class="form-text">{{description}}</div>
        <div v-if="showerr" class="form-text text-danger">
          <ul>
            <li v-for="log in logs" v-bind:key="log">
              <b>{{ log.severity }}</b> {{ log.msg }}
            </li>
          </ul>
        </div>
      </form>
</template>
<script>
    export default {
    name: 'DialogConfigminiform',
    props: {
      title: String,
      table: String,
      value: String,
      action: String,
      description: String,
      dbid: Number,
      deleteform: Boolean
    },
    data() {
      return{
        showerr: false,
        logs: []
      }
    },
    methods: {
      submit(){
        /*console.log("Value: " + this.$refs.input.value);
        console.log("DBID: " + this.$refs.dbid.value);*/
        const url = new URL(window.location.protocol + "//" + window.location.hostname + "/rezepte/" + this.action);
        url.searchParams.append(this.table, this.$refs.input.value);
        if(this.deleteform && this.$refs.dbid.value.length > 0){
          url.searchParams.append("delete", this.$refs.dbid.value);
        }else{
          url.searchParams.append("insert", this.$refs.input.value);
        }

        console.log("My URL is: " + url.href);

        const req = new XMLHttpRequest();
        req.addEventListener("load", () =>{
          /*
              Write all logs into a array. it gets further processed by VUE
          */
          let jobj = JSON.parse(req.responseText);
          this.logs = [];
          this.showerr = false;
          if(jobj){
            jobj.logs.forEach(log => {
              this.logs.push(log);
              this.showerr = true;
            });
          }else{
            this.logs.push({
              severity: "Critical",
              msg: "JSON parse fehler!"
            });
            console.log(req.responseText);
            this.showerr = true;
          }
        });

        req.addEventListener("loadend", () => {
          this.$emit(this.table + "-update");
          console.log("Sending " + this.table + "-update");
        });
        req.open("GET", url.href);
        req.send();
      }
    }
}
</script>