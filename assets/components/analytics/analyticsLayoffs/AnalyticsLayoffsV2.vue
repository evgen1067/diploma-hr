<template>
  <template v-if="!loading">
    <div class="d-flex flex-column">
      <div class="chart-container pl-3 pr-3">
        <div class="row mb-3 mt-3">
          <div class="col-4">
            <div class="row">
              <div class="col-6">
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
                    <va-card color="#4056A1" gradient>
                      <va-card-content>
                        <h3 class="va-h3" style="color: white">{{ layoffsInfo.totalDismissed }}</h3>
                        <p style="color: white">уволено</p>
                      </va-card-content>
                    </va-card>
                  </div>
                </div>
              </div>
              <div class="col-6">
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
                  <va-card color="#4056A1" gradient>
                    <va-card-content>
                      <h3 class="va-h3" style="color: white">{{ layoffsInfo.avgWorkExp }}</h3>
                      <p style="color: white">стаж работы</p>
                    </va-card-content>
                  </va-card>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <va-card>
              <va-card-content>
                <Bar
                  style="height: 400px"
                  :chart-data="layoffsInfo.reasonChart"
                  :chart-options="{ ...chartOptions, ...horizontalBarOptions }"
                />
              </va-card-content>
            </va-card>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <va-card>
              <va-card-content>
                <Bar
                  style="height: 800px"
                  :chart-data="layoffsInfo.departmentChart"
                  :chart-options="{ ...chartOptions, ...horizontalBarOptions }"
                />
              </va-card-content>
            </va-card>
          </div>
          <div class="col-6">
            <va-card>
              <va-card-content>
                <Bar
                  style="height: 800px"
                  :chart-data="layoffsInfo.positionChart"
                  :chart-options="{ ...chartOptions, ...horizontalBarOptions }"
                />
              </va-card-content>
            </va-card>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <va-card>
              <va-card-content>
                <Bar
                  :chart-data="layoffsInfo.workExpChart"
                  :chart-options="{ ...chartOptions, ...horizontalBarOptions }"
                />
              </va-card-content>
            </va-card>
          </div>
          <div class="col-6">
            <va-card>
              <va-card-content>
                <Bar
                  :chart-data="layoffsInfo.categoryChart"
                  :chart-options="{ ...chartOptions, ...horizontalBarOptions }"
                />
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
import { Bar } from 'vue-chartjs';
import { VaCard, VaCardContent, VaDateInput, VaPopover, VaSelect } from 'vuestic-ui';
import { ChartApi } from '../../../api/chart/ChartApi';
import HrSpinner from '../../../ui/hrSpinner/HrSpinner';
import { defaultConfig } from '../ChartConfig';
import cloneDeep from 'lodash.clonedeep';

export default {
  name: 'AnalyticsLayoffsV2',
  components: { VaCardContent, VaCard, VaPopover, VaDateInput, VaSelect, HrSpinner, Bar },
  async created() {
    this.chartOptions = defaultConfig;
    this.filter.valueTo = new Date();
    this.filter.valueFrom = new Date();
    this.filter.valueFrom.setMonth(this.filter.valueFrom.getMonth() - 36);
    this.defaultDate = {
      valueFrom: this.filter.valueFrom.toLocaleDateString(),
      valueTo: this.filter.valueTo.toLocaleDateString(),
    };
    await this.updateChartData();
  },
  data: () => ({
    defaultDate: {
      valueFrom: '',
      valueTo: '',
    },
    filter: {
      valueFrom: '',
      valueTo: '',
    },
    loading: true,
    layoffsInfo: null,
    chartOptions: null,
    horizontalBarOptions: {
      indexAxis: 'y',
      elements: {
        bar: {
          borderWidth: 1,
        },
      },
    },
  }),
  methods: {
    async updateChartData() {
      this.loading = true;
      this.layoffsInfo = await ChartApi.getLayoffsInfo(this.clearFilter());
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

p {
  margin-bottom: 0;
}
.va-card__content {
  padding: 0.8rem;
}
</style>
