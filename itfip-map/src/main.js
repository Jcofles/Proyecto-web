import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router/index.js' // Agregamos el /index.js para ser específicos

const app = createApp(App)

app.use(router)
app.mount('#app')