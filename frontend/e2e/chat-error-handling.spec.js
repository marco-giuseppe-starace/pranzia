import { test, expect } from '@playwright/test'
import { DEMO_QR_TOKEN } from './fixtures.js'

// Non assumiamo se ANTHROPIC_API_KEY e' configurata o meno in questo
// ambiente: verifichiamo solo che, in caso di errore, l'utente veda il
// messaggio generico pulito e MAI il dettaglio grezzo dell'eccezione
// Claude API (vedi docs/ia-guardrail.md e AiController::safeCall). Se la
// chiave e' configurata e la chiamata riesce, il test passa comunque.
test('la chat non mostra mai il dettaglio grezzo di un\'eccezione Claude API', async ({ page }) => {
  await page.goto(`/t/${DEMO_QR_TOKEN}`)
  await page.waitForURL('**/menu')

  await page.goto('/chat')
  await page.getByPlaceholder('Fai una domanda sul menu...').fill('Ci sono piatti senza glutine?')
  await Promise.all([
    page.waitForResponse((res) => res.url().includes('/api/ai/ask')),
    page.getByRole('button', { name: 'Invia' }).click(),
  ])

  // Una risposta (vera o messaggio d'errore pulito) deve comunque arrivare.
  const messages = page.locator('.messages')
  await expect(messages.locator('.assistant')).toHaveCount(1, { timeout: 10_000 })
  await expect(messages).not.toContainText('Anthropic')
  await expect(messages).not.toContainText('authentication_error')
  await expect(messages).not.toContainText('Exception')
})
