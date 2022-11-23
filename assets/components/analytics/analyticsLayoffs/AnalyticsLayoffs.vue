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
                    <hr-card :value="layoffsInfo.totalDismissed" description="уволено" color="#F13C20" />
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
                  <hr-card :value="layoffsInfo.avgWorkExp" description="стаж работы" color="#4056A1" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <va-card>
              <va-card-content>
                <hr-chart :data="layoffsInfo.reasonChart" type="horizontal-bar" />
              </va-card-content>
            </va-card>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <va-card>
              <va-card-content>
                <hr-chart style="height: 800px" :data="layoffsInfo.departmentChart" type="horizontal-bar" />
              </va-card-content>
            </va-card>
          </div>
          <div class="col-6">
            <va-card>
              <va-card-content>
                <hr-chart style="height: 800px" :data="layoffsInfo.positionChart" type="horizontal-bar" />
              </va-card-content>
            </va-card>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <va-card>
              <va-card-content>
                <hr-chart :data="layoffsInfo.workExpChart" type="horizontal-bar" />
              </va-card-content>
            </va-card>
          </div>
          <div class="col-6">
            <va-card>
              <va-card-content>
                <hr-chart :data="layoffsInfo.categoryChart" type="horizontal-bar" />
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
import HrCard from "../../../ui/hrCard/HrCard";

export default {
  name: 'AnalyticsLayoffs',
  components: {HrCard, HrChart, VaCardContent, VaCard, VaPopover, VaDateInput, HrSpinner },
  async created() {
    this.defaultSettings();
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
    loading: false,
    layoffsInfo: null,
  }),
  methods: {
    // настройки по умолчанию
    defaultSettings() {
      this.filter.valueTo = new Date();
      this.filter.valueFrom = new Date();
      this.filter.valueFrom.setMonth(this.filter.valueFrom.getMonth() - 36);
      this.defaultDate = {
        valueFrom: this.filter.valueFrom.toLocaleDateString(),
        valueTo: this.filter.valueTo.toLocaleDateString(),
      };
    },
    // обновление данных по увольнениям
    async updateChartData() {
      if (!this.loading) {
        this.loading = true;
        this.layoffsInfo = await ChartApi.getLayoffsInfo(this.clearFilter());
        this.loading = false;
      }
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
      };
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
