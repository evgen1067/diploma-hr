<template>
  <va-modal v-model="show" size="large">
    <template #content>
      <div class="d-flex flex-column">
        <div class="row">
          <div class="col-6">
            <img src="@/assets/employee.png" class="w-100" alt="">
          </div>
          <div class="col-6">
            <slot></slot>
          </div>
        </div>
      </div>
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
