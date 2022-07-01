<template>
    <div class="modal d-block" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Rezept Löschen?</h5>
            <button @click="$emit('hide-modal')" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p v-if="finished">{{ response }}</p>
            <p v-else>Willst du das Rezept <i class="text-danger">{{ name }}</i> wirklich löschen?</p>
        </div>
        <div v-if="finished" class="modal-footer">
            <button @click="$emit('hide-modal')" type="button" class="btn btn-success">OK</button>
        </div>
        <div v-else class="modal-footer">
            <button @click="$emit('hide-modal')" type="button" class="btn btn-secondary">Abbrechen</button>
            <a @click="deleteRecipe" type="button" class="btn btn-danger">Löschen</a>
        </div>
        </div>
    </div>
    </div>
</template>

<script>

export default {
  name: 'DeleteModal',
  data(){
    return {
        finished: false,
        response: "ERROR"
    };
  },
  props: {
    url: String,
    name: String,
  },
  methods:{
    deleteRecipe(){
        var vueref = this;
        var xhr = new XMLHttpRequest();
        xhr.open("GET", this.url, false);
        xhr.onreadystatechange = function () {
            if (this.readyState === XMLHttpRequest.DONE) {
                if (this.status === 200) {
                    vueref.response = this.responseText;
                    vueref.finished = true;
                }
            }
        };
        xhr.send();
    }
  }
}
</script>