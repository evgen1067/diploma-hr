<template>
  <template v-if="!loading">
    <div class="row">
      <div class="col-12 mb-3">
        <div class="d-flex flex-column">
          <va-content class="d-flex align-items-center mb-2">
            <va-icon name="settings" />
            <h5>Настройка</h5>
          </va-content>
          <div class="d-flex align-items-center">
            <va-date-input
              v-model="filter.valueFrom"
              label="От"
              class="mb-3 mr-1 w-100"
              manual-input
              clearable
              @update:model-value="fetchData"
              @clear="fetchData"
              :reset-on-close="false"
            />
            <va-date-input
              v-model="filter.valueTo"
              label="До"
              class="mb-3 mr-1 w-100"
              manual-input
              clearable
              @update:model-value="fetchData"
              @clear="fetchData"
              :reset-on-close="false"
            />
            <va-select
              label="Отдел"
              v-model="filter.department"
              :options="departmentOptions"
              clearable
              class="mb-3 mr-1 w-100"
              color="#6D39CC"
              @update:model-value="fetchData"
            ></va-select>
          </div>
        </div>
      </div>
      <div class="col-12 mb-3">
        <Line :chart-options="{ ...chartOptions }" :chart-data="chartData" type="line" />
      </div>
      <div class="col-12">
        <div class="d-flex flex-column">
          <va-content>
            <h5 class="mb-1">Количество сотрудников на текущий момент времени: {{ turnoverData.totalNumber }}.</h5>
            <h5 class="mb-1">
              Изменения по численности (принято - {{ turnoverData.acceptedNumber }}, уволено -
              {{ turnoverData.dismissedNumber }}, декрет - {{ turnoverData.decreeNumber }}).
            </h5>
            <h5 class="mb-1">Среднесписочная численность: {{ turnoverData.averageNumber }}.</h5>
            <h5 class="mb-1">Коэффициент текучести: {{ turnoverData.turnoverRatio }}.</h5>
            <h5 class="mb-1">Коэффициент добровольной текучести: {{ turnoverData.turnoverVoluntarilyRatio }}.</h5>
            <h5 class="mb-1">Коэффициент принудительной текучести: {{ turnoverData.turnoverForcedRatio }}.</h5>
            <h5 class="mb-1">Коэффициент нежелательной текучести: {{ turnoverData.turnoverUndesirableRatio }}.</h5>
          </va-content>
        </div>
      </div>
    </div>
  </template>
  <template v-else>
    <hr-spinner />
  </template>
</template>

<script>
import HrSpinner from '../../../ui/hrSpinner/HrSpinner';
import { ChartApi } from '../../../api/chart/ChartApi';
import { Line } from 'vue-chartjs';
import { defaultConfig } from '../ChartConfig';
import { VaContent, VaDateInput, VaIcon, VaSelect } from 'vuestic-ui';
import cloneDeep from "lodash.clonedeep";

export default {
  name: 'TurnoverRates',
  components: { VaSelect, VaDateInput, VaIcon, VaContent, HrSpinner, Line },
  data: () => ({
    loading: true,
    chartOptions: null,
    turnoverData: null,
    chartData: null,
    departmentOptions: null,
    filter: {
      valueFrom: null,
      valueTo: null,
      department: null,
    },
  }),
  async created() {
    this.chartOptions = defaultConfig;
    this.filter.valueTo = new Date();
    this.filter.valueFrom = new Date();
    this.filter.valueFrom.setMonth(this.filter.valueFrom.getMonth() - 1);
    await this.fetchData();
    this.filter.department = this.departmentOptions[0];
  },
  methods: {
    async fetchData() {
      this.loading = true;

      this.turnoverData = await ChartApi.getTurnoverInfo(this.clearFilter());
      this.departmentOptions = await ChartApi.getDepartmentsInfo();
      this.departmentOptions.unshift('По всей компании');
      this.chartData = this.turnoverData.averageNumberDataChart;
      this.$nextTick(() => {
        this.loading = false;
      });
    },
    clearFilter() {
      let filter = cloneDeep(this.filter);
      if (!filter.valueFrom) {
        delete filter.valueFrom;
      } else {
        filter.valueFrom = filter.valueFrom.toLocaleDateString();
      }
      if (!filter.valueTo) {
        delete filter.valueTo;
      } else {
        filter.valueTo = filter.valueTo.toLocaleDateString();
      }
      if (!filter.department || filter.department === 'По всей компании') {
        delete filter.department;
      }
      return filter;
    },
  },
};
</script>

<style scoped></style>
