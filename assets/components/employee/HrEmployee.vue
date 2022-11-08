<template>
  <va-content class="content">
    <h3 class="fw-bold">Управление сотрудниками</h3>
    <div class="d-flex align-center">
      <va-button icon="person_add" color="primary" class="mr-3 mb-2">Добавить</va-button>
      <va-button icon="clear" color="danger" class="mr-3 mb-2">Удалить</va-button>
    </div>
  </va-content>
</template>

<script>
import { VaButton, VaContent } from 'vuestic-ui';
export default {
  name: 'HrEmployee',
  components: { VaButton, VaContent },
  created() {
    // this.toggleLoading(true);
    //
    // this.toggleLoading(false);
  },
  methods: {
    toggleLoading(value) {
      this.$store.dispatch('toggleLoading', value);
    },
  },
};
</script>

<style scoped></style>
