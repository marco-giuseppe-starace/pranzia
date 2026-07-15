import { test, expect } from '@playwright/test'
import { DEMO_QR_TOKEN } from './fixtures.js'

test.describe('Flusso ordine cliente', () => {
  test('landing -> menu -> filtro allergeni -> carrello -> invio ordine', async ({ page }) => {
    // 1. Landing: la scansione del QR avvia la sessione e redirige al menu.
    await page.goto(`/t/${DEMO_QR_TOKEN}`)
    await page.waitForURL('**/menu')
    await expect(page.getByText('Antipasti')).toBeVisible()

    // 2. Filtro allergeni: escludere il glutine nasconde i piatti che lo
    // contengono (dato verificato in DB, vedi docs/ia-guardrail.md).
    await page.locator('summary').click()
    await Promise.all([
      page.waitForResponse((res) => res.url().includes('exclude_allergens=') && res.status() === 200),
      page.getByLabel('Glutine').check(),
    ])
    await expect(page.getByText('Bruschetta al pomodoro')).toHaveCount(0)
    await page.getByLabel('Glutine').uncheck()
    await page.waitForResponse((res) => res.url().includes('/api/menu') && res.status() === 200)

    // 3. Aggiungi un piatto al carrello.
    await page.getByRole('button', { name: 'Aggiungi' }).first().click()
    await expect(page.locator('.cart-badge')).toHaveText('1')

    // 4. Invia l'ordine dal carrello.
    await page.goto('/cart')
    await expect(page.getByText('Il tuo ordine')).toBeVisible()
    const total = await page.locator('.total').innerText()
    await Promise.all([
      page.waitForResponse((res) => res.url().includes('/api/orders') && res.request().method() === 'POST'),
      page.getByRole('button', { name: 'Invia ordine' }).click(),
    ])

    // 5. Il carrello si svuota e appare lo stato del nuovo ordine.
    await expect(page.getByText('Il carrello e\' vuoto.')).toHaveCount(0)
    await expect(page.getByText('In attesa')).toBeVisible()
    expect(total).toContain('€')
  })
})
