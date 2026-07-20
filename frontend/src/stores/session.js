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
    // Nessuno dei due e' persistito: vanno sempre riletti dal server (cambiano
    // quando lo staff incassa, o quando un altro telefono sullo stesso tavolo
    // inserisce i coperti), un valore in cache in localStorage rischierebbe
    // di restare vecchio per sempre su quel browser.
    paid: false,
    // true = lo staff ha inviato un'anteprima del conto dalla cassa (prima
    // ancora dell'incasso vero e proprio): fa comparire la voce "Ricevuta"
    // anche se paid e' ancora false.
    receiptSent: false,
    // null = non ancora inseriti dal cliente: fa comparire il modal
    // bloccante prima di poter ordinare (vedi GuestsModal.vue).
    guests: null,
    // true = riapre il modal per correggere un valore gia' inserito
    // (stavolta annullabile): impostato dal pulsante "modifica" in
    // AppHeader.vue, letto da GuestsModal.vue in App.vue.
    guestsModalForceOpen: false,
    ...loadPersisted(),
  }),
  actions: {
    setSession({ id, table_id: tableId, table_number: tableNumber, paid, receipt_sent: receiptSent, guests }) {
      this.sessionId = id
      this.tableId = tableId
      this.tableNumber = tableNumber
      this.paid = paid ?? false
      this.receiptSent = receiptSent ?? false
      this.guests = guests ?? null
      this.persist()
    },
    setLanguage(language) {
      this.language = language
      this.persist()
    },
    // Interrogato a intervalli dal componente header (vedi AppHeader.vue)
    // per mostrare la voce "Ricevuta" non appena lo staff incassa il
    // tavolo, e per sapere se un altro telefono dello stesso tavolo ha
    // gia' inserito i coperti.
    //
    // Guardia anti-race: se sessionId cambia mentre questa richiesta e'
    // in volo (es. la pagina di atterraggio sta aprendo/riprendendo la
    // sessione giusta proprio in quel momento), la risposta arrivata per
    // l'id vecchio va scartata invece di sovrascrivere lo stato piu'
    // recente.
    async refreshStatus() {
      const requestedFor = this.sessionId
      if (!requestedFor) return
      const response = await api.get(`/sessions/${requestedFor}/status`)
      if (this.sessionId !== requestedFor) return
      this.paid = response.paid
      this.receiptSent = response.receipt_sent
      this.guests = response.guests
    },
    async updateGuests(guests) {
      await api.patch(`/sessions/${this.sessionId}/guests`, { guests })
      this.guests = guests
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
