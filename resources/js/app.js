import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import i18n from './locales';
import App from './App.vue';

const app = createApp(App);

const pinia = createPinia();
app.use(pinia);
app.use(router);
app.use(i18n);

// Initialize locale/RTL
import { useLocaleStore } from './stores/locale';
const localeStore = useLocaleStore(pinia);
localeStore.init();

// Initialize auth token from localStorage
import axios from 'axios';
const token = localStorage.getItem('token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

app.mount('#app');

