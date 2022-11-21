<template>
  <va-content class="content">
    <h3 class="fw-bolder text-center">Показатели текучести</h3>
  </va-content>
  <turnover-rates />
</template>

<script>
import { VaContent } from 'vuestic-ui';
import TurnoverRates from '../components/analytics/turnoverRates/TurnoverRates';
export default {
  name: 'TurnoverView',
  components: { TurnoverRates, VaContent },
};
</script>

<style scoped></style>
