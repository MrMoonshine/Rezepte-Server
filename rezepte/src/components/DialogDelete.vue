<template>
  <dialog ref="dialog" class="p-4 overflow-auto shadow-lg rounded bg-light">
    <div class="d-flex justify-content-between d-print none">
      <h2 class="text-danger flex-grow-1">Rezept Löschen?</h2>
      <button @click="close" type="button" class="btn-close mt-2" aria-label="Close"></button>
    </div>
    <div>
        <p>Willst du wirklich das Rezept {{ name }} löschen?</p>
        <div class="d-flex gap-2">
            <button @click='emitAndClose' class="btn btn-danger">Rezept Löschen</button>
            <button @click="close" class="btn btn-secondary">Abbrechen</button>
        </div>
    </div>
  </dialog>
</template>

<script>

export default {
  name: 'DialogDelete',
  data(){
    return {
    };
  },
  props: {
    name: String,
  },
  methods:{
    showDialog() {
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.showModal();
    },
    close() {
      // Raw DOM of dialog must be accessed: therefore the ref attribute is set
      this.$refs.dialog.close();
    },
    emitAndClose(){
      this.$emit("delete-confirm");
      this.close();
    }
  }
}
</script>