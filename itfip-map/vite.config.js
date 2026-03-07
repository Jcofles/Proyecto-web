import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    host: true,
    allowedHosts: [
      'solomon-lookup-edward-joe.trycloudflare.com'
    ],
    hmr: {
      host: 'solomon-lookup-edward-joe.trycloudflare.com',
      clientPort: 443,
      protocol: 'wss'
    }
  }
})
