export const defaultConfig = {
  responsive: true,
  scales: {
    y: {
      ticks: {
        display: true,
        font: {
          fontSize: 12,
          family: 'Montserrat Alternates',
        },
      },
    },
    x: {
      ticks: {
        display: false,
        font: {
          fontSize: 8,
          family: 'Montserrat Alternates',
        },
      },
    },
  },
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        font: {
          family: 'Montserrat Alternates',
          size: 14,
        },
        usePointStyle: true,
      },
    },
    tooltip: {
      titleFont: {
        size: 14,
        family: 'Montserrat Alternates',
      },
      bodyFont: {
        size: 14,
        family: 'Montserrat Alternates',
      },
      boxPadding: 4,
    },
  },
  datasets: {
    line: {
      tension: 0.3,
      borderColor: '#4056A1',
    },
    bubble: {
      borderColor: 'transparent',
    },
    bar: {
      borderColor: 'transparent',
    },
  },
  maintainAspectRatio: false,
  animation: true,
};

