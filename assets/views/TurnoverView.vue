<template>
  <hr-heading>Показатели текучести</hr-heading>
  <turnover-rates />
</template>

<script>
import TurnoverRates from '@/components/analytics/turnoverRates/TurnoverRates';
import HrHeading from '@/ui/hrHeading/HrHeading';
export default {
  name: 'TurnoverView',
  components: { HrHeading, TurnoverRates },
};
</script>

<style scoped></style>
