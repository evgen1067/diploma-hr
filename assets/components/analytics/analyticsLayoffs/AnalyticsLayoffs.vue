<template>
  <template v-if="!loading">
    <div class="d-flex flex-column">
      <va-content class="content">
        <h3 class="text-center mb-3">Аналитика причин увольнений</h3>
      </va-content>
      <div class="d-flex align-items-center filter-container mb-4">
        <div class="row">
          <div class="d-flex flex-row align-items-center">
            <va-select
              label="Отдел"
              v-model="filter.department"
              :options="departmentOptions"
              clearable
              class="mb-3 mr-3"
              color="#6D39CC"
              @update:model-value="updateChartData"
            ></va-select>
            <va-select
              label="Период"
              v-model="filter.range"
              :options="rangeOptions"
              clearable
              class="mb-3 mr-3"
              color="#6D39CC"
              @update:model-value="updateChartData"
            ></va-select>
            <va-select
              label="Стаж работы"
              v-model="filter.work"
              :options="workExpOptions"
              clearable
              class="mb-3 mr-3"
              color="#6D39CC"
              @update:model-value="updateChartData"
            ></va-select>
          </div>
        </div>
      </div>
      <div class="chart-container mb-3">
        <div class="row">
          <div class="col-12">
            <Bar
              v-for="(data, key) in chartData"
              :key="key"
              :chart-data="data"
              :chart-options="{ ...chartOptions, ...horizontalBarOptions }"
            />
          </div>
        </div>
      </div>
    </div>
  </template>
  <template v-else>
    <hr-spinner />
  </template>
</template>

<script>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);
import { VaContent, VaSelect } from 'vuestic-ui';
import { ChartApi } from '../../../api/chart/ChartApi';
import HrSpinner from '../../../ui/hrSpinner/HrSpinner';
import { defaultConfig } from '../ChartConfig';
import cloneDeep from 'lodash.clonedeep';

export default {
  name: 'AnalyticsLayoffs',
  components: { VaSelect, HrSpinner, VaContent, Bar },
  async created() {
    await this.updateChartData();
  },
  data: () => ({
    filter: {
      range: '',
      department: '',
      work: '',
    },
    loading: true,
    chartData: null,
    chartOptions: null,
    horizontalBarOptions: {
      indexAxis: 'y',
      elements: {
        bar: {
          borderWidth: 1,
        },
      },
    },
    departmentOptions: null,
    rangeOptions: ['Месяц', 'Квартал', 'Год'],
    workExpOptions: ['меньше 3х месяцев', 'до 1 года работы', 'до 3х лет работы', 'свыше 3х лет работы'],
  }),
  methods: {
    async updateChartData() {
      this.loading = true;
      this.chartData = await ChartApi.getLayoffsInfo(this.clearFilter());
      this.departmentOptions = await ChartApi.getDepartmentsInfo();
      this.departmentOptions.unshift('По всей компании');
      this.chartOptions = defaultConfig;
      this.loading = false;
    },
    clearFilter() {
      let filter = cloneDeep(this.filter);
      if (filter.department === 'По всей компании' || filter.department === '') delete filter.department;
      if (filter.range === '') delete filter.range;
      if (filter.work === '') delete filter.work;
      return filter;
    },
  },
};
</script>

<style scoped>
.filter-container {
  margin-top: 20px;
}
</style>
