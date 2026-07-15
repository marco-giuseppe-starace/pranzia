// Helper condivisi dai test E2E. Il tavolo di prova (DEMO_QR_TOKEN) viene
// creato dallo script "pretest:e2e" (package.json) tramite il comando
// artisan "e2e:seed-table", non da questi test - servirebbe altrimenti un
// endpoint pubblico di creazione tavoli, che non deve esistere.
export const DEMO_QR_TOKEN = 'demo-qr-e2e'
export const API_URL = process.env.PLAYWRIGHT_API_URL ?? 'http://pranzia.test/api'
export const ADMIN_EMAIL = process.env.PLAYWRIGHT_ADMIN_EMAIL ?? 'test@example.com'
export const ADMIN_PASSWORD = process.env.PLAYWRIGHT_ADMIN_PASSWORD ?? 'password'

// Login diretto via API (non attraverso la UI) per i test che devono
// partire gia' autenticati o compiere setup lato admin prima di navigare.
export async function loginAsAdmin(request) {
  const response = await request.post(`${API_URL}/admin/login`, {
    data: { email: ADMIN_EMAIL, password: ADMIN_PASSWORD },
  })
  const body = await response.json()
  return body.token
}
