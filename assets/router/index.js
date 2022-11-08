import { createRouter, createWebHashHistory } from 'vue-router';
import HomeView from '@/views/HomeView';
import TurnoverRates from '@/components/analytics/turnoverRates/TurnoverRates';
import AnalyticsLayoffs from '@/components/analytics/analyticsLayoffs/AnalyticsLayoffs';
import ForecastingStaffing from '@/components/analytics/forecastingStaffing/ForecastingStaffing';
const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
  },
  {
    path: '/turnover/rates',
    name: 'turnover_rates',
    component: TurnoverRates,
  },
  {
    path: '/analytics/layoffs',
    name: 'analytics_layoffs',
    component: AnalyticsLayoffs,
  },
  {
    path: '/forecasting/staffing',
    name: 'forecasting_staffing',
    component: ForecastingStaffing,
  },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

export default router;
