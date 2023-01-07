<template>
  <component :is="chartComponent" :chart-data="data" :chart-options="chartOptions" class="va-chart"/>
</template>

<script>
import { defaultConfig, chartTypesMap } from '@/ui/hrChart/ChartConfig';

export default {
  name: 'HrChart',
  props: {
    type: {
      type: String,
      required: true,
    },
    data: {
      type: Object,
      required: true,
    },
  },
  computed: {
    chartComponent() {
      return chartTypesMap[this.type];
    },
    chartOptions() {
      return { ...defaultConfig };
    },
  },
};
</script>

<style lang="scss" scoped>
.va-chart {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  > * {
    height: 100%;
    width: 100%;
  }
  canvas {
    width: 100%;
    height: auto;
    min-height: 320px;
  }
}
</style>
