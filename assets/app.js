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

import '@fontsource/montserrat-alternates';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle';

import './styles/style.css';

const app = withUUID(createApp(App));
app.use(store);
app.use(router);
app.use(createVuestic({}));
app.mount('#app');
