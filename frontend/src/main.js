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
      console.log('Attempting to register service worker...');
      const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
      
      // Check if the service worker is active
      if (registration.active) {
        console.log('Service worker is active');
      } else if (registration.installing) {
        console.log('Service worker is installing');
        registration.installing.addEventListener('statechange', (e) => {
          console.log('Service worker state changed:', e.target.state);
        });
      } else if (registration.waiting) {
        console.log('Service worker is waiting');
      }
      
      // Listen for updates
      registration.addEventListener('updatefound', () => {
        console.log('New service worker found, installing...');
        const newWorker = registration.installing;
        newWorker.addEventListener('statechange', () => {
          console.log('New service worker state:', newWorker.state);
        });
      });
      
    } catch (err) {
      console.error('ServiceWorker registration failed: ', err);
    }
  });
}

app.mount('#app');