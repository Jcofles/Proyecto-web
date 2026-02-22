import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    allowedHosts: [
      'soon-vanitied-unripplingly.ngrok-free.dev' // El dominio que te dio el error
    ]
  }
})