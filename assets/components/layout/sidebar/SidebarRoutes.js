export default {
  routes: [
    {
      title: 'Управление сотрудниками',
      routeName: 'employee',
      meta: {
        icon: 'badge',
      },
    },
    {
      title: 'Показатели текучести',
      routeName: 'turnover_rates',
      meta: {
        icon: 'bar_chart',
      },
    },
    {
      title: 'Аналитика причин увольнений',
      routeName: 'analytics_layoffs',
      meta: {
        icon: 'query_stats',
      },
    },
    {
      title: 'Прогнозирование кадровой потребности',
      routeName: 'forecasting_staffing',
      meta: {
        icon: 'insights',
      },
    },
  ],
};
