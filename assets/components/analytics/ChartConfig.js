export const defaultConfig = {
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        font: {
          color: '#34495e',
          family: 'sans-serif',
          size: 14,
        },
        usePointStyle: true,
      },
    },
    tooltip: {
      bodyFont: {
        size: 14,
        family: 'sans-serif',
      },
      boxPadding: 4,
    },
  },
  maintainAspectRatio: false,
  animation: true,
};
