import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';
import { createVuestic } from 'vuestic-ui';
import 'vuestic-ui/styles/essential.css';
import 'vuestic-ui/styles/grid.css';
import 'vuestic-ui/styles/reset.css';
import 'vuestic-ui/styles/typography.css';

import withUUID from 'vue-uuid';

import '@fontsource/montserrat-alternates/100.css';
import '@fontsource/montserrat-alternates/200.css';
import '@fontsource/montserrat-alternates/300.css';
import '@fontsource/montserrat-alternates/400.css';
import '@fontsource/montserrat-alternates/500.css';
import '@fontsource/montserrat-alternates/600.css';
import '@fontsource/montserrat-alternates/700.css';
import '@fontsource/montserrat-alternates/800.css';
import '@fontsource/montserrat-alternates/900.css';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle';

import { library } from '@fortawesome/fontawesome-svg-core';

import {
  faEquals,
  faNotEqual,
  faLessThan,
  faLessThanEqual,
  faGreaterThan,
  faGreaterThanEqual,
  faFont,
  faCalendarDay,
  faHouse,
} from '@fortawesome/free-solid-svg-icons';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

library.add(
  faEquals,
  faNotEqual,
  faLessThan,
  faLessThanEqual,
  faGreaterThan,
  faGreaterThanEqual,
  faFont,
  faCalendarDay,
  faHouse,
);

import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler,
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, LineElement, LinearScale, PointElement, CategoryScale, Filler);
import './styles/style.css';

const app = withUUID(createApp(App));
app.use(store);
app.use(router);
app.use(createVuestic({}));
app.component('font-awesome-icon', FontAwesomeIcon);
app.mount('#app');
