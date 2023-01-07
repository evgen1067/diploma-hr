<template>
  <va-content class="content">
    <h3 class="fw-bolder text-center">
      <slot></slot>
    </h3>
  </va-content>
</template>

<script>
import { VaContent } from 'vuestic-ui';
export default {
  name: 'HrHeading',
  components: { VaContent },
};
</script>

<style scoped></style>
