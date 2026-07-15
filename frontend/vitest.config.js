import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

// Config di test separata da vite.config.js: il plugin PWA non serve nei
// test e potrebbe interferire con l'ambiente jsdom.
export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'jsdom',
    // La cartella e2e/ contiene i test Playwright (npm run test:e2e), non
    // vanno raccolti da Vitest.
    exclude: ['e2e/**', 'node_modules/**'],
  },
})
