import { createApp } from 'vue';
import './style.css';
import App from './App.vue';
import router from "./router.js";
import { createPinia } from "pinia";
import 'core-js/stable';
import '@fortawesome/fontawesome-free/css/all.min.css';


const pinia = createPinia();
const app = createApp(App);

app.use(router);
app.use(pinia);

app.config.errorHandler = (err, vm, info) => {
  console.error('Erreur globale Vue :', err);
};

app.mount('#app');
