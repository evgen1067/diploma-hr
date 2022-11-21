<template>
  <va-content class="content">
    <h3 class="fw-bolder text-center">Аналитика причин увольнений</h3>
  </va-content>
  <analytics-layoffs />
</template>

<script>
import AnalyticsLayoffs from '../components/analytics/analyticsLayoffs/AnalyticsLayoffs';
import { VaContent } from 'vuestic-ui';
export default {
  name: 'LayoffsView',
  components: { VaContent, AnalyticsLayoffs },
};
</script>

<style scoped></style>
