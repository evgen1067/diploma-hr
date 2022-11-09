<template>
  <va-modal v-model="show" size="large">
    <template #content>
      <slot></slot>
    </template>
  </va-modal>
</template>

<script>
import { VaModal } from 'vuestic-ui';
export default {
  name: 'HrModal',
  components: { VaModal },
  data: () => ({
    show: false,
  }),
  methods: {
    openModal() {
      this.show = true;
    },
    closeModal() {
      this.show = false;
    },
  },
};
</script>

<style scoped></style>
