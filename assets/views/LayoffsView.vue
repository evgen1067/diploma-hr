<template>
  <hr-heading>Аналитика причин увольнений</hr-heading>
  <analytics-layoffs />
</template>

<script>
import AnalyticsLayoffs from '@/components/analytics/analyticsLayoffs/AnalyticsLayoffs';
import HrHeading from '@/ui/hrHeading/HrHeading';
export default {
  name: 'LayoffsView',
  components: { HrHeading, AnalyticsLayoffs },
};
</script>

<style scoped></style>
