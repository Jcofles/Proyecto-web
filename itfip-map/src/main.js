import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router/index.js'
import "primeicons/primeicons.css";

// 1. Importar PrimeVue y el tema
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';

const app = createApp(App)

// 2. Configurar PrimeVue con el tema Aura
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.my-app-dark', // Útil si decides poner modo oscuro después
        }
    }
});

app.use(router)
app.mount('#app')