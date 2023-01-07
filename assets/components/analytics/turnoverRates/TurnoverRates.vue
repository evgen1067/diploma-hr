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
                </div>
              </div>
              <div class="col-4">
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
          <div class="col-2">
            <hr-card :value="turnoverData.totalNumber" description="число сотрудников" color="#4056A1" />
          </div>
          <div class="col-2">
            <hr-card :value="turnoverData.acceptedNumber" description="принято" color="success" />
          </div>
          <div class="col-2">
            <hr-card :value="turnoverData.dismissedNumber" description="уволено" color="#F13C20" />
          </div>
          <div class="col-2">
            <hr-card :value="turnoverData.decreeNumber" description="декрет" color="#F37A48" />
          </div>
          <div class="col-2">
            <hr-card :value="turnoverData.averageNumber" description="Среднеспис. числ." color="#30B5C8" />
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <va-card>
              <va-card-content>
                <hr-chart :data="averageNumberDataChart" type="line" />
              </va-card-content>
            </va-card>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-4">
            <va-card>
              <va-card-content>
                <hr-chart :data="turnoverChart" type="bar" />
              </va-card-content>
            </va-card>
          </div>
          <div class="col-4">
            <va-card>
              <va-card-content>
                <hr-chart :data="workExperienceChart" type="horizontal-bar" />
              </va-card-content>
            </va-card>
          </div>
          <div class="col-4">
            <va-card>
              <va-card-content>
                <hr-chart :data="genderChart" type="bar" />
              </va-card-content>
            </va-card>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <va-card>
              <va-card-content>
                <hr-chart style="height: 800px" :data="departmentChart" type="horizontal-bar" />
              </va-card-content>
            </va-card>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
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
import { ChartApi } from '@/api/chart/ChartApi';
import { EmployeeApi } from '@/api/employee/EmployeeApi';
import { cloneDeep } from 'lodash';
import randomColor from 'randomcolor';
import { VaCard, VaCardContent, VaDateInput, VaPopover } from 'vuestic-ui';
import HrChart from '@/ui/hrChart/HrChart';
import HrSpinner from '@/ui/hrSpinner/HrSpinner';
import HrCard from '@/ui/hrCard/HrCard';

export default {
  name: 'TurnoverRates',
  components: { HrCard, HrChart, VaCardContent, VaCard, VaPopover, VaDateInput, HrSpinner },
  data: () => ({
    defaultDate: {
      valueFrom: '',
      valueTo: '',
    },
    loading: false,
    turnoverData: null,
    filter: {
      valueFrom: null,
      valueTo: null,
      department: null,
    },
    departmentsList: [],
  }),
  async created() {
    this.loading = true;
    await this.defaultSettings();
    await this.updateChartData();
    this.loading = false;
  },
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
      this.turnoverData = await ChartApi.getTurnoverInfo(this.clearFilter());
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
        department: this.filter.department,
      };
    },
    getColors(len) {
      return randomColor({
        count: len,
        luminosity: 'bright',
        hue: 'random',
      });
    },
    getTurnoverChart(data, label, colors = true) {
      let chartInfo = {
        labels: [],
        datasets: [
          {
            label: label,
            backgroundColor: colors ? this.getColors(data.length) : this.getColors(1),
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
    turnoverChart() {
      if (this.turnoverData?.turnoverChart) {
        return this.getTurnoverChart(this.turnoverData.turnoverChart, 'Коэффициент текучести');
      } else return null;
    },
    averageNumberDataChart() {
      if (this.turnoverData?.averageNumberDataChart) {
        return this.getTurnoverChart(
          this.turnoverData.averageNumberDataChart,
          'Списочная численность в компании',
          false,
        );
      } else return null;
    },
    workExperienceChart() {
      if (this.turnoverData?.workExperienceChart) {
        return this.getTurnoverChart(this.turnoverData.workExperienceChart, 'Коэффициент текучести от стажа работы');
      } else return null;
    },
    departmentChart() {
      if (this.turnoverData?.departmentChart) {
        return this.getTurnoverChart(this.turnoverData.departmentChart, 'Коэффициент текучести от должности');
      } else return null;
    },
    positionChart() {
      if (this.turnoverData?.positionChart) {
        return this.getTurnoverChart(this.turnoverData.positionChart, 'Коэффициент текучести от отдела');
      } else return null;
    },
    genderChart() {
      if (this.turnoverData?.genderChart) {
        return this.getTurnoverChart(this.turnoverData.genderChart, 'Коэффициент текучести от пола');
      } else return null;
    },
  },
};
</script>

<style scoped></style>
