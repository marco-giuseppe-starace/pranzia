import { test, expect } from '@playwright/test'
import { ADMIN_EMAIL, ADMIN_PASSWORD, DEMO_QR_TOKEN } from './fixtures.js'

test.describe('Pannello ristoratore', () => {
  test('rotte /admin/* richiedono login', async ({ page }) => {
    await page.goto('/admin/orders')
    await page.waitForURL('**/admin/login')
  })

  test('login -> dashboard ordini -> avanzamento stato', async ({ page }) => {
    // Prerequisito: un ordine in attesa. Lo creiamo dal lato cliente per non
    // dipendere da fixture dirette sul DB (stesso giro del test cliente).
    await page.goto(`/t/${DEMO_QR_TOKEN}`)
    await page.waitForURL('**/menu')
    await page.getByRole('button', { name: 'Aggiungi' }).first().click()
    await page.goto('/cart')
    await Promise.all([
      page.waitForResponse((res) => res.url().includes('/api/orders') && res.request().method() === 'POST'),
      page.getByRole('button', { name: 'Invia ordine' }).click(),
    ])

    await page.goto('/admin/login')
    await page.getByLabel('Email').fill(ADMIN_EMAIL)
    await page.getByLabel('Password').fill(ADMIN_PASSWORD)
    await page.getByRole('button', { name: 'Accedi' }).click()
    await page.waitForURL('**/admin/orders')

    // Nessun header cliente (carrello/lingua) sulle pagine admin.
    await expect(page.locator('.language-switcher')).toHaveCount(0)

    const pendingCard = page.locator('.order-card.pending').first()
    await expect(pendingCard).toBeVisible()
    await Promise.all([
      page.waitForResponse((res) => res.url().includes('/status') && res.request().method() === 'PATCH'),
      pendingCard.getByRole('button').click(),
    ])
    await expect(page.locator('.order-card.preparing').first()).toBeVisible()
  })

  test('gestione menu: un piatto creato dall\'admin appare sul menu cliente', async ({ page }) => {
    await page.goto('/admin/login')
    await page.getByLabel('Email').fill(ADMIN_EMAIL)
    await page.getByLabel('Password').fill(ADMIN_PASSWORD)
    await page.getByRole('button', { name: 'Accedi' }).click()
    await page.waitForURL('**/admin/orders')

    await page.goto('/admin/menu')
    await page.waitForResponse((res) => res.url().includes('/admin/menu-categories') && res.request().method() === 'GET')

    const dishName = `Piatto E2E ${Date.now()}`
    await page.getByPlaceholder('Nome piatto').fill(dishName)
    await page.getByPlaceholder('Prezzo').fill('4.20')
    await page.locator('.inline-form select').selectOption({ index: 1 })
    await Promise.all([
      page.waitForResponse((res) => res.url().includes('/admin/menu-items') && res.request().method() === 'POST'),
      page.getByRole('button', { name: 'Aggiungi piatto' }).click(),
    ])
    await expect(page.getByText(dishName)).toBeVisible()

    await page.goto(`/t/${DEMO_QR_TOKEN}`)
    await page.waitForURL('**/menu')
    await expect(page.getByText(dishName)).toBeVisible()
  })

  test('report costi IA si apre senza errori', async ({ page }) => {
    await page.goto('/admin/login')
    await page.getByLabel('Email').fill(ADMIN_EMAIL)
    await page.getByLabel('Password').fill(ADMIN_PASSWORD)
    await page.getByRole('button', { name: 'Accedi' }).click()
    await page.waitForURL('**/admin/orders')

    await page.goto('/admin/ai-costs')
    await expect(page.getByRole('heading', { name: 'Costi IA' })).toBeVisible()
  })
})
