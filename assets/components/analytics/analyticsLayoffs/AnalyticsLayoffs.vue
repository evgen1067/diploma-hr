<template>
  <template v-if="!loading">
    <div class="d-flex flex-column">
      <div class="chart-container pl-3 pr-3">
        <div class="row mb-3 mt-3">
          <div class="col-8">
            <div class="row">
              <div class="col-3">
                <div class="d-flex flex-column">
                  <div class="mb-3">
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
                  <div class="mb-3">
                    <hr-card :value="layoffsInfo.totalDismissed" description="уволено" color="#F13C20" />
                  </div>
                </div>
              </div>
              <div class="col-3">
                <div class="d-flex flex-column">
                  <div class="mb-3">
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
                  <hr-card :value="layoffsInfo.avgWorkExp" description="стаж работы" color="#4056A1" />
                </div>
              </div>
              <div class="col-6">
                <div class="d-flex flex-column">
                  <div class="mb-3">
                    <va-popover
                      icon="info"
                      color="#4056A1"
                      title="Фильтрация по компании"
                      :hover-out-timeout="30"
                      message="По умолчанию — компания"
                      placement="right"
                      open
                    >
                      <va-select
                        style="--va-select-option-list-option-min-height: auto"
                        v-model="filter.department"
                        label="Отдел"
                        :options="departmentsList"
                        text-by="label"
                        value-by="value"
                        class="w-100 mb-3"
                        clearable
                        color="#4056A1"
                        @update:model-value="updateChartData"
                      >
                      </va-select>
                    </va-popover>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <va-card>
              <va-card-content>
                <hr-chart :data="reasonChart" type="horizontal-bar" />
              </va-card-content>
            </va-card>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <va-card>
              <va-card-content>
                <hr-chart :data="workExpChart" type="bar" />
              </va-card-content>
            </va-card>
          </div>
          <div class="col-6">
            <va-card>
              <va-card-content>
                <hr-chart :data="categoryChart" type="bar" />
              </va-card-content>
            </va-card>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-5">
            <va-card>
              <va-card-content>
                <hr-chart style="height: 800px" :data="departmentChart" type="horizontal-bar" />
              </va-card-content>
            </va-card>
          </div>
          <div class="col-7">
            <va-card>
              <va-card-content>
                <hr-chart style="height: 800px" :data="positionChart" type="horizontal-bar" />
              </va-card-content>
            </va-card>
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
import { VaCard, VaCardContent, VaDateInput, VaPopover } from 'vuestic-ui';
import { ChartApi } from '@/api/chart/ChartApi';
import { cloneDeep } from 'lodash';
import HrSpinner from '@/ui/hrSpinner/HrSpinner';
import HrChart from '@/ui/hrChart/HrChart';
import HrCard from '@/ui/hrCard/HrCard';
import { EmployeeApi } from '@/api/employee/EmployeeApi';
import randomColor from 'randomcolor';

export default {
  name: 'AnalyticsLayoffs',
  components: { HrCard, HrChart, VaCardContent, VaCard, VaPopover, VaDateInput, HrSpinner },
  async created() {
    this.loading = true;
    await this.defaultSettings();
    await this.updateChartData();
    this.loading = false;
  },
  data: () => ({
    defaultDate: {
      valueFrom: '',
      valueTo: '',
    },
    filter: {
      valueFrom: '',
      valueTo: '',
      department: null,
    },
    loading: false,
    layoffsInfo: null,
    departmentsList: [],
  }),
  methods: {
    toast(message, color) {
      this.$vaToast.init({
        message: message,
        color: color,
        position: 'bottom-right',
      });
    },
    // настройки по умолчанию
    async defaultSettings() {
      this.filter.valueTo = new Date();
      this.filter.valueFrom = new Date();
      this.filter.valueFrom.setMonth(this.filter.valueFrom.getMonth() - 36);
      this.defaultDate = {
        valueFrom: this.filter.valueFrom.toLocaleDateString(),
        valueTo: this.filter.valueTo.toLocaleDateString(),
      };
      let namesDepartment = await EmployeeApi.getEmployeesDepartments();
      this.departmentsList.push({
        label: 'Компания',
        value: null,
      });
      for (let i in namesDepartment) {
        this.departmentsList.push({
          label: namesDepartment[i].department,
          value: namesDepartment[i].department,
        });
      }
    },
    // обновление данных по увольнениям
    async updateChartData() {
      this.loading = true;
      this.layoffsInfo = await ChartApi.getLayoffsInfo(this.clearFilter());
      this.loading = false;
      this.toast('Данные загружены', 'success');
    },
    // очистка фильтра
    clearFilter() {
      let filter = cloneDeep(this.filter);
      if (filter.valueTo < filter.valueFrom) {
        filter.valueFrom = new Date();
        filter.valueFrom.setMonth(filter.valueFrom.getMonth() - 36);
        filter.valueTo = new Date();
      }
      if (!filter.valueFrom) {
        filter.valueFrom = new Date();
        filter.valueFrom.setMonth(filter.valueFrom.getMonth() - 36);
      }
      if (!filter.valueTo) {
        filter.valueTo = new Date();
      }
      this.filter = cloneDeep(filter);
      return {
        valueFrom: filter.valueFrom.toLocaleDateString(),
        valueTo: filter.valueTo.toLocaleDateString(),
        department: filter.department,
      };
    },
    getColors(len) {
      return randomColor({
        count: len,
        luminosity: 'bright',
        hue: 'random',
      });
    },
    getLayoffsChart(data, label = 'Количество увольнений') {
      let chartInfo = {
        labels: [],
        datasets: [
          {
            label: label,
            backgroundColor: this.getColors(data.length),
            borderWidth: 1,
            data: [],
          },
        ],
      };
      for (let i in data) {
        chartInfo.labels.push(data[i].key);
        chartInfo.datasets[0].data.push(data[i].value);
      }
      return chartInfo;
    },
  },
  computed: {
    reasonChart() {
      if (this.layoffsInfo?.reasonChart) {
        return this.getLayoffsChart(this.layoffsInfo.reasonChart);
      } else return null;
    },
    departmentChart() {
      if (this.layoffsInfo?.departmentChart) {
        return this.getLayoffsChart(this.layoffsInfo.departmentChart);
      } else return null;
    },
    positionChart() {
      if (this.layoffsInfo?.positionChart) {
        return this.getLayoffsChart(this.layoffsInfo.positionChart);
      } else return null;
    },
    workExpChart() {
      if (this.layoffsInfo?.workExpChart) {
        return this.getLayoffsChart(this.layoffsInfo.workExpChart);
      } else return null;
    },
    categoryChart() {
      if (this.layoffsInfo?.categoryChart) {
        return this.getLayoffsChart(this.layoffsInfo.categoryChart);
      } else return null;
    },
  },
};
</script>

<style lang="scss" scoped>
p {
  margin-bottom: 0;
}
.va-card {
  &__content {
    padding: 0.8rem;
  }
}
</style>
