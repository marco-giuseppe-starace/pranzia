import { defineStore } from 'pinia'

const STORAGE_KEY = 'pranzia.cart'

function loadPersisted() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY)) ?? []
  } catch {
    return []
  }
}

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: loadPersisted(),
  }),
  getters: {
    count: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    total: (state) => state.items.reduce((sum, item) => sum + item.price * item.quantity, 0),
  },
  actions: {
    add(menuItem) {
      const existing = this.items.find((item) => item.menuItemId === menuItem.id)
      if (existing) {
        existing.quantity += 1
      } else {
        this.items.push({
          menuItemId: menuItem.id,
          name: menuItem.name,
          price: Number(menuItem.price),
          quantity: 1,
          notes: '',
        })
      }
      this.persist()
    },
    updateQuantity(menuItemId, quantity) {
      const item = this.items.find((item) => item.menuItemId === menuItemId)
      if (!item) return

      if (quantity <= 0) {
        this.remove(menuItemId)
        return
      }

      item.quantity = quantity
      this.persist()
    },
    remove(menuItemId) {
      this.items = this.items.filter((item) => item.menuItemId !== menuItemId)
      this.persist()
    },
    clear() {
      this.items = []
      this.persist()
    },
    persist() {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items))
    },
  },
})
