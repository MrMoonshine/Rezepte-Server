<template>
    <form class="mb-3" method="POST" :action="action">
        <label v-if="title" for="table + value + 'Input'" class="form-label">{{title}}</label>
        <div class="input-group">
          <input ref="input" type="text" class="form-control" :id="table + value + 'Input'" :name="table" :value="value" :aria-describedby="table + 'Help'" required>
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
        // Abort if empty form is being sumbitted
        if(
          this.$refs.dbid.value.length < 1 && this.deleteform ||
          this.$refs.input.value.length < 0
          ){
          console.warn("Input is empty, aborting database request!");
          return;
        }

        const url = new URL(window.location.protocol + "//" + window.location.hostname + "/rezepte/" + this.action);
        const formData = new FormData();
        const req = new XMLHttpRequest();
        
        if(this.deleteform){
          // It is a delete-form and the input str is not empty
          formData.append("delete", this.table);
          formData.append("id", String(this.$refs.dbid.value));
        }else{
          // otherwise
          formData.append("insert", this.table);
          formData.append("name", this.$refs.input.value);
        }
        // Always select the table afterwards, to not make an additional request
        url.searchParams.append("select", this.table);

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
          // Forward received data to 
          if(this.logs.length > 0){
            console.warn("Received "+this.logs.length+" Logs, aborting DB update!");
            return;
          }
          // No logs, update table
          this.$emit("configdb-update", jobj.data ?? []);
          console.log("Sending configdb-update");
        });

        req.open("POST", url.href);
        req.send(formData);
      }
    }
}
</script>