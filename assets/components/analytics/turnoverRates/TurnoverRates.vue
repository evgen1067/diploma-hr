<template>
  <template v-if="!loading">
    <div class="row">
      <div class="col-12 mb-3 pr-3 pl-3">
        <div class="d-flex flex-column">
          <div class="d-flex pr-3 pl-3 align-items-center">
            <div class="col-12 pr-3 pl-3 d-flex flex-row align-items-center">
              <div class="mr-3 mb-2">
                <va-popover
                  icon="info"
                  color="#4056A1"
                  title="Дата начала периода"
                  :hover-out-timeout="30"
                  :message="`По умолчанию — ${defaultDate.valueFrom}`"
                  placement="bottom-start"
                  open
                >
                  <va-date-input
                    v-model="filter.valueFrom"
                    label="От"
                    manual-input
                    clearable
                    @update:model-value="fetchData"
                    @clear="fetchData"
                    :reset-on-close="false"
                  />
                </va-popover>
              </div>

              <div class="mr-3 mb-2">
                <va-popover
                  icon="info"
                  color="#4056A1"
                  title="Дата конца периода"
                  :hover-out-timeout="30"
                  :message="`По умолчанию — ${defaultDate.valueTo}`"
                  placement="bottom-start"
                  open
                >
                  <va-date-input
                    v-model="filter.valueTo"
                    label="До"
                    manual-input
                    clearable
                    @update:model-value="fetchData"
                    @clear="fetchData"
                    :reset-on-close="false"
                  />
                </va-popover>
              </div>

              <div class="mr-3 mb-2">
                <va-popover
                  icon="info"
                  color="#4056A1"
                  title="Фильтрация по компании"
                  :hover-out-timeout="30"
                  message="По умолчанию — по всей компании"
                  placement="bottom-start"
                  open
                >
                  <va-select
                    label="Отдел"
                    v-model="filter.department"
                    :options="departmentOptions"
                    clearable
                    color="#4056A1"
                    @update:model-value="fetchData"
                  ></va-select>
                </va-popover>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 mb-3">
        <Line class="hr-chart" :chart-options="{ ...chartOptions }" :chart-data="chartData" type="line" />
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
import { VaContent, VaDateInput, VaIcon, VaPopover, VaSelect } from 'vuestic-ui';
import cloneDeep from 'lodash.clonedeep';

export default {
  name: 'TurnoverRates',
  components: { VaPopover, VaSelect, VaDateInput, VaIcon, VaContent, HrSpinner, Line },
  data: () => ({
    defaultDate: {
      valueFrom: '',
      valueTo: '',
    },
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
    this.defaultDate = {
      valueFrom: this.filter.valueFrom.toLocaleDateString(),
      valueTo: this.filter.valueTo.toLocaleDateString(),
    };
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

<style scoped>
.hr-chart {
  height: 340px;
}
</style>
