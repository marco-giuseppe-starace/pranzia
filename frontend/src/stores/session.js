import { defineStore } from 'pinia'
import { api } from '../api/client.js'

const STORAGE_KEY = 'pranzia.session'

function loadPersisted() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY)) ?? {}
  } catch {
    return {}
  }
}

export const useSessionStore = defineStore('session', {
  state: () => ({
    sessionId: null,
    tableId: null,
    tableNumber: null,
    language: 'it',
    // Non persistito: va sempre riletto dal server (cambia quando lo
    // staff incassa da "In cassa"), un valore in cache in localStorage
    // rischierebbe di restare "non pagato" per sempre su quel browser.
    paid: false,
    ...loadPersisted(),
  }),
  actions: {
    setSession({ id, table_id: tableId, table_number: tableNumber, paid }) {
      this.sessionId = id
      this.tableId = tableId
      this.tableNumber = tableNumber
      this.paid = paid ?? false
      this.persist()
    },
    setLanguage(language) {
      this.language = language
      this.persist()
    },
    // Interrogato a intervalli dal componente header (vedi AppHeader.vue)
    // per mostrare la voce "Ricevuta" non appena lo staff incassa il
    // tavolo, senza bisogno che il cliente ricarichi la pagina.
    async refreshPaidStatus() {
      if (!this.sessionId) return
      const response = await api.get(`/sessions/${this.sessionId}/status`)
      this.paid = response.paid
    },
    persist() {
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          sessionId: this.sessionId,
          tableId: this.tableId,
          tableNumber: this.tableNumber,
          language: this.language,
        })
      )
    },
  },
})
