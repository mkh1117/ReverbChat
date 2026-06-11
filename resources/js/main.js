import './bootstrap';
import '../css/app.css';
import { createApp } from 'vue';
import App from './Pages/main.vue/index.js';

const app = createApp(App);
app.mount('#app');
