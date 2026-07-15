import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

// Config di test separata da vite.config.js: il plugin PWA non serve nei
// test e potrebbe interferire con l'ambiente jsdom.
export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'jsdom',
  },
})
