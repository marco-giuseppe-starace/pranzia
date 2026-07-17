<script setup>
import { useRoute } from 'vue-router'
import AppHeader from './components/AppHeader.vue'

// Le rotte /admin/* hanno il proprio layout (AdminLayout), niente header
// cliente (carrello, lingua, nav menu/chat). La home (elenco QR demo) non
// ha ancora una sessione tavolo attiva, quindi niente header nemmeno li'.
const route = useRoute()
const showHeader = () => route.path !== '/' && !route.path.startsWith('/admin')
</script>

<template>
  <AppHeader v-if="showHeader()" />
  <RouterView />
</template>
