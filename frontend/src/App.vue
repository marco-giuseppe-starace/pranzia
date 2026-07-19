<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import ConfirmDialog from './components/ConfirmDialog.vue'
import { useCartStore, cartStorageKey } from './stores/cart.js'
import { useSessionStore } from './stores/session.js'

// Le rotte /admin/* hanno il proprio layout (AdminLayout), niente header
// cliente (carrello, lingua, nav menu/chat). La home (elenco QR demo) non
// ha ancora una sessione tavolo attiva, quindi niente header nemmeno li'.
const route = useRoute()
const showHeader = () => route.path !== '/' && !route.path.startsWith('/admin')

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
</template>
