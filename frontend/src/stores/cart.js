import { defineStore } from 'pinia'
import { useSessionStore } from './session.js'

// Il carrello e' legato alla sessione (non una chiave fissa globale): due
// tavoli/sessioni diverse sullo stesso telefono non devono mai vedere il
// carrello l'uno dell'altro, ed evita che un carrello vecchio ricompaia
// riaprendo il sito per un tavolo nuovo.
export function cartStorageKey(sessionId) {
  return `pranzia.cart.${sessionId ?? 'anonymous'}`
}

function loadPersisted(sessionId) {
  try {
    return JSON.parse(localStorage.getItem(cartStorageKey(sessionId))) ?? []
  } catch {
    return []
  }
}

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: loadPersisted(useSessionStore().sessionId),
  }),
  getters: {
    count: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    total: (state) => state.items.reduce((sum, item) => sum + item.price * item.quantity, 0),
  },
  actions: {
    add(menuItem, notes = '') {
      const existing = this.items.find((item) => item.menuItemId === menuItem.id)
      if (existing) {
        existing.quantity += 1
        // Una nota specificata esplicitamente (es. dal pop-up "Chiedi
        // all'IA") sovrascrive quella precedente per questa riga.
        if (notes) existing.notes = notes
      } else {
        this.items.push({
          menuItemId: menuItem.id,
          name: menuItem.name,
          description: menuItem.description,
          group: menuItem.group,
          price: Number(menuItem.price),
          quantity: 1,
          notes,
        })
      }
      this.persist()
    },
    updateNotes(menuItemId, notes) {
      const item = this.items.find((item) => item.menuItemId === menuItemId)
      if (!item) return

      item.notes = notes
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
      localStorage.setItem(cartStorageKey(useSessionStore().sessionId), JSON.stringify(this.items))
    },
    // Richiamato quando un'altra tab con la stessa sessione (stesso
    // storage: es. piu' tab aperte sullo stesso telefono/browser) modifica
    // il carrello, cosi' questa tab non resta con uno stato vecchio in
    // memoria finche' non viene ricaricata (vedi listener 'storage' in
    // App.vue).
    syncFromStorage() {
      this.items = loadPersisted(useSessionStore().sessionId)
    },
  },
})
