<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary d-flex d-print-none">
        <a class="navbar-brand" href="#"><img src="/favicon.ico" alt="">Alpakaküche</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggleExternalContent"
            aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarToggleExternalContent">
            <div class="navbar-nav">
                <a class="btn nav-item nav-link active" href="#">Rezepte </a>
                <a class="btn nav-item nav-link" href="/">Home</a>
                <button @click="$refs.dialogconfig.showDialog()" class="btn nav-item nav-link">Config</button>
            </div>
        </div>
    </nav>
    <!--<DialogConfig v-if="show_config_modal" @hide-modal="show_config_modal = false"></DialogConfig>-->
    <DialogConfig ref="dialogconfig"></DialogConfig>
</template>
<script>
import DialogConfig from './DialogConfig.vue'
export default {
    name: 'NavBar',
    components:{
        DialogConfig
    },
    data() {

    },
    methods: {

    }
}
</script>