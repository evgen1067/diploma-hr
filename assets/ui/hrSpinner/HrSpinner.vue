<template>
  <breeding-rhombus-spinner :animation-duration="2000" :size="250" color="#154ec1" />
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
