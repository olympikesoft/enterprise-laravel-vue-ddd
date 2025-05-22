// resources/js/app.js
import './bootstrap.js';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';

// Vuetify
import vuetify from './plugins/vuetify';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

import App from './App.vue';
import router from './router';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(vuetify); // Add Vuetify to the app

const authStore = useAuthStore();

authStore.initializeAuth().then(() => {
    app.mount('#app');
}).catch(error => {
    console.error("Critical error during app initialization:", error);
    app.mount('#app');
});
