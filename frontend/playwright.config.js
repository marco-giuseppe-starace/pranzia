import { defineConfig, devices } from '@playwright/test'

// Suite E2E: verifica il flusso reale nel browser (cliente e admin) contro
// un backend Laravel vero, non contro mock. baseURL/apiURL sono
// configurabili via env perche' in CI puntano al preview server e al
// backend avviati durante il job, in locale a Vite dev + Laragon.
export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['html', { open: 'never' }]] : 'list',
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:5173',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  ],
})
