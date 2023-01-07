import { createRouter, createWebHashHistory } from 'vue-router';
import HomeView from '../views/HomeView';
import EmployeeView from '../views/EmployeeView';
import TurnoverView from '../views/TurnoverView';
import LayoffsView from '../views/LayoffsView';
import ForecastView from '../views/ForecastView';
const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
  },
  {
    path: '/employee',
    name: 'employee',
    component: EmployeeView,
  },
  {
    path: '/turnover/rates',
    name: 'turnover_rates',
    component: TurnoverView,
  },
  {
    path: '/analytics/layoffs',
    name: 'analytics_layoffs',
    component: LayoffsView,
  },
  {
    path: '/forecasting/staffing',
    name: 'forecasting_staffing',
    component: ForecastView,
  },
];

const router = createRouter({
  history: createWebHashHistory(),
  routes,
});

export default router;
