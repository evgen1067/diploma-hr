<template>
  <div class="d-flex align-items-center align-content-center justify-content-center p-5">
    <breeding-rhombus-spinner :animation-duration="2000" :size="150" color="#4056A1" />
  </div>
</template>

<script>
import { BreedingRhombusSpinner } from 'epic-spinners';
export default {
  name: 'HrSpinner',
  components: {
    BreedingRhombusSpinner,
  },
};
</script>

<style scoped></style>
