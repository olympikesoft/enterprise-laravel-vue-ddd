import './bootstrap';

import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from './App.vue';
import router from './router';
import { useAuthStore } from './stores/auth';


const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

const authStore = useAuthStore();

authStore.initializeAuth().then(() => {
    app.mount('#app');
}).catch(error => {
    console.error("Critical error during app initialization:", error);
    app.mount('#app');
});
