<script setup>
import { useRoute } from 'vue-router'
import AppHeader from './components/AppHeader.vue'

// Le rotte /admin/* hanno il proprio layout (AdminLayout), niente header
// cliente (carrello, lingua, nav menu/chat).
const route = useRoute()
</script>

<template>
  <AppHeader v-if="!route.path.startsWith('/admin')" />
  <RouterView />
</template>
