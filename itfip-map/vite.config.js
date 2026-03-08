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
      'assisted-guarantees-frontpage-hottest.trycloudflare.com'
    ],
    hmr: {
      host: 'assisted-guarantees-frontpage-hottest.trycloudflare.com',
      clientPort: 443,
      protocol: 'wss'
    }
  }
})
