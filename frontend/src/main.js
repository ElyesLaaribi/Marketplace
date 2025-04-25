// main.js
import { createApp } from 'vue';
import './style.css';
import App from './App.vue';
import router from "./router.js";
import { createPinia } from "pinia";
import 'core-js/stable';
import '@fortawesome/fontawesome-free/css/all.min.css';
import ToastPlugin from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-bootstrap.css';
import VueSocialChat from 'vue-social-chat'
import 'vue-social-chat/dist/style.css'

const pinia = createPinia();
const app = createApp(App);

app.use(router);
app.use(pinia);
app.use(ToastPlugin);
app.use(VueSocialChat)

app.config.errorHandler = (err, vm, info) => {
  console.error('Erreur globale Vue :', err);
};

// Register service worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', async () => {
    try {
      const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
    } catch (err) {
      console.log('ServiceWorker registration failed: ', err);
    }
  });
}

app.mount('#app');