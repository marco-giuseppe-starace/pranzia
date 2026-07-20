<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import ConfirmDialog from './components/ConfirmDialog.vue'
import GuestsModal from './components/GuestsModal.vue'
import { useCartStore, cartStorageKey } from './stores/cart.js'
import { useSessionStore } from './stores/session.js'

// Le rotte /admin/* hanno il proprio layout (AdminLayout), niente header
// cliente (carrello, lingua, nav menu/chat). La home (elenco QR demo) e la
// schermata di apertura sessione (/t/:qrToken, "Apertura sessione...") non
// hanno ancora una sessione tavolo stabilita, quindi niente header nemmeno
// li': evita anche che l'header lanci una richiesta di stato con l'id di
// sessione vecchio (rimasto in localStorage da una visita precedente)
// mentre la pagina di atterraggio ne sta aprendo/riprendendo una nuova,
// che altrimenti in rari casi arrivava per prima e faceva
// apparire/sparire il modal coperti con dati sbagliati per un istante.
const route = useRoute()
const showHeader = () => route.path !== '/' && route.name !== 'landing' && !route.path.startsWith('/admin')

// Stesse rotte dell'header cliente: il modal coperti non ha senso in
// admin/home (nessuna sessione tavolo attiva li').
const showGuestsModal = () => {
  if (!showHeader() || !session.sessionId) return false
  return session.guests === null || session.guestsModalForceOpen
}

// Se piu' tab condividono lo stesso storage (stesso browser, stessa
// sessione: es. piu' tab aperte per errore, o test con piu' finestre in
// incognito), un ordine inviato da una tab non aggiornava le altre finche'
// non venivano ricaricate. L'evento 'storage' scatta nelle ALTRE tab
// quando una modifica il carrello: qui risincronizziamo questa.
const cart = useCartStore()
const session = useSessionStore()

function handleStorage(event) {
  if (event.key === cartStorageKey(session.sessionId)) {
    cart.syncFromStorage()
  }
}

onMounted(() => window.addEventListener('storage', handleStorage))
onUnmounted(() => window.removeEventListener('storage', handleStorage))
</script>

<template>
  <AppHeader v-if="showHeader()" />
  <RouterView />
  <ConfirmDialog />
  <GuestsModal v-if="showGuestsModal()" :dismissable="session.guests !== null" />
</template>
