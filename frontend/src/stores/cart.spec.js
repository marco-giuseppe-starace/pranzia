import { beforeEach, describe, expect, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCartStore } from './cart.js'

describe('cart store', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
  })

  it('adds a new item with quantity 1', () => {
    const cart = useCartStore()

    cart.add({ id: 1, name: 'Bruschetta', price: '6.50' })

    expect(cart.items).toHaveLength(1)
    expect(cart.items[0]).toMatchObject({ menuItemId: 1, quantity: 1, price: 6.5 })
  })

  it('increments quantity when adding the same item again', () => {
    const cart = useCartStore()

    cart.add({ id: 1, name: 'Bruschetta', price: '6.50' })
    cart.add({ id: 1, name: 'Bruschetta', price: '6.50' })

    expect(cart.items).toHaveLength(1)
    expect(cart.items[0].quantity).toBe(2)
  })

  it('removes the item when quantity drops to zero', () => {
    const cart = useCartStore()
    cart.add({ id: 1, name: 'Bruschetta', price: '6.50' })

    cart.updateQuantity(1, 0)

    expect(cart.items).toHaveLength(0)
  })

  it('computes count and total across multiple lines', () => {
    const cart = useCartStore()
    cart.add({ id: 1, name: 'Bruschetta', price: '6.50' })
    cart.add({ id: 2, name: 'Acqua', price: '2.00' })
    cart.updateQuantity(1, 3)

    expect(cart.count).toBe(4)
    expect(cart.total).toBeCloseTo(3 * 6.5 + 2.0)
  })

  it('persists items to localStorage and reloads them in a new store instance', () => {
    const cart = useCartStore()
    cart.add({ id: 1, name: 'Bruschetta', price: '6.50' })

    setActivePinia(createPinia())
    const reloaded = useCartStore()

    expect(reloaded.items).toHaveLength(1)
    expect(reloaded.items[0].name).toBe('Bruschetta')
  })
})
