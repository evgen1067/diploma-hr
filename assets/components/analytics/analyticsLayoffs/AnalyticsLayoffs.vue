<template>
  <template v-if="!loading">
    <div class="d-flex flex-column">
      <div class="d-flex align-items-center filter-container mb-4">
        <div class="row">
          <div class="col-12 pr-3 pl-3 flex-column">
            <div class="col-12 pl-3 pr-3 d-flex flex-row align-items-center">
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
                    @update:model-value="updateChartData"
                    @clear="updateChartData"
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
                    @update:model-value="updateChartData"
                    @clear="updateChartData"
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
                    @update:model-value="updateChartData"
                  ></va-select>
                </va-popover>
              </div>

              <div class="mr-3 mb-2">
                <va-popover
                  icon="info"
                  color="#4056A1"
                  title="Фильтрация по стажу работы"
                  :hover-out-timeout="30"
                  message="По умолчанию — не зависит от стажа"
                  placement="bottom-start"
                  open
                >
                  <va-select
                    label="Стаж работы"
                    v-model="filter.work"
                    :options="workExpOptions"
                    clearable
                    color="#4056A1"
                    @update:model-value="updateChartData"
                  ></va-select>
                </va-popover>
              </div>
            </div>
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
              class="hr-chart"
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
import { VaDateInput, VaPopover, VaSelect } from 'vuestic-ui';
import { ChartApi } from '../../../api/chart/ChartApi';
import HrSpinner from '../../../ui/hrSpinner/HrSpinner';
import { defaultConfig } from '../ChartConfig';
import cloneDeep from 'lodash.clonedeep';

export default {
  name: 'AnalyticsLayoffs',
  components: { VaPopover, VaDateInput, VaSelect, HrSpinner, Bar },
  async created() {
    this.chartOptions = defaultConfig;
    this.filter.valueTo = new Date();
    this.filter.valueFrom = new Date();
    this.filter.valueFrom.setMonth(this.filter.valueFrom.getMonth() - 1);
    this.filter.work = this.workExpOptions[0];
    this.defaultDate = {
      valueFrom: this.filter.valueFrom.toLocaleDateString(),
      valueTo: this.filter.valueTo.toLocaleDateString(),
    };
    await this.updateChartData();
    this.filter.department = this.departmentOptions[0];
  },
  data: () => ({
    defaultDate: {
      valueFrom: '',
      valueTo: '',
    },
    filter: {
      valueFrom: '',
      valueTo: '',
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
    workExpOptions: ['Не зависит', 'меньше 3х месяцев', 'до 1 года работы', 'до 3х лет работы', 'свыше 3х лет работы'],
  }),
  methods: {
    async updateChartData() {
      this.loading = true;
      this.chartData = await ChartApi.getLayoffsInfo(this.clearFilter());
      this.departmentOptions = await ChartApi.getDepartmentsInfo();
      this.departmentOptions.unshift('По всей компании');
      this.loading = false;
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
      if (filter.department === 'По всей компании' || filter.department === '') {
        delete filter.department;
      }
      if (filter.work === '' || filter.work === 'Не зависит') {
        delete filter.work;
      }
      return filter;
    },
  },
};
</script>

<style scoped>
.filter-container {
  margin-top: 20px;
}

.hr-chart {
  height: 340px;
}
</style>
