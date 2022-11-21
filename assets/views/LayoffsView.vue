<template>
  <va-content class="content">
    <h3 class="fw-bolder text-center">Аналитика причин увольнений</h3>
  </va-content>
  <analytics-layoffs-v2 />
</template>

<script>
import { VaContent } from 'vuestic-ui';
import AnalyticsLayoffsV2 from '../components/analytics/analyticsLayoffs/AnalyticsLayoffsV2';
export default {
  name: 'LayoffsView',
  components: { AnalyticsLayoffsV2, VaContent },
};
</script>

<style scoped></style>
