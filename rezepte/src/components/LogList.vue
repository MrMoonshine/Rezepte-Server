<template>
    <ul>
        <li v-for="log in logs" v-bind:key="log" :class="this.severity_css_class(log.severity)">
            <b>{{ log.severity }}</b> {{ log.msg }}
        </li>
    </ul>
</template>

<script>

export default {
  name: 'LogList',
  data(){
    return {
        
    };
  },
  props: {
    logs: Array
  },
  methods:{
    severity_css_class(severity){
        let csscls = "";
        switch (severity) {
            case "Critical":
                csscls = "text-danger";
                break;
            case "Unknown":
                csscls = "text-secondary";
                break;
            case "Warning":
                csscls = "text-warning";
                break;
            case "Info":
                csscls = "text-info";
                break;
            default:
                csscls = "";
                break;
        }
        return csscls;
    }
  }
}
</script>