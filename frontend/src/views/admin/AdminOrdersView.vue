<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { api } from '../../api/client.js'
import { useAdminAuthStore } from '../../stores/adminAuth.js'
import AdminLayout from '../../layouts/AdminLayout.vue'
import OrderCard from '../../components/admin/OrderCard.vue'

const adminAuth = useAdminAuthStore()
const orders = ref([])
const statusFilter = ref('')
// Id degli ordini con un cambio di stato in corso: disabilita il relativo
// pulsante finche' la richiesta non e' completata, cosi' un doppio click
// non fa avanzare lo stato due volte mentre si attende la risposta.
const advancingIds = ref([])
let pollTimer = null
// Guardia anti-race: il polling ogni 5s e un refresh manuale (dopo
// "avanza stato") possono avere richieste in volo contemporaneamente; senza
// questo contatore, la risposta piu' lenta puo' arrivare per ultima e
// sovrascrivere lo stato con dati vecchi anche se la richiesta piu' recente
// ha gia' risposto.
let requestSeq = 0

async function loadOrders() {
  const currentRequest = ++requestSeq
  const query = statusFilter.value ? `?status=${statusFilter.value}` : ''
  const response = await api.get(`/admin/orders${query}`, { token: adminAuth.token })
  if (currentRequest === requestSeq) {
    orders.value = response.data
  }
}

async function advance(orderId, status) {
  if (advancingIds.value.includes(orderId)) return
  advancingIds.value.push(orderId)

  try {
    await api.patch(`/admin/orders/${orderId}/status`, { status }, { token: adminAuth.token })
    await loadOrders()
  } finally {
    advancingIds.value = advancingIds.value.filter((id) => id !== orderId)
  }
}

onMounted(() => {
  loadOrders()
  // Polling piu' frequente del carrello cliente: la cucina ha bisogno di
  // vedere i nuovi ordini quasi in tempo reale.
  pollTimer = setInterval(loadOrders, 5_000)
})

onUnmounted(() => clearInterval(pollTimer))
</script>

<template>
  <AdminLayout>
    <h1>Dashboard ordini</h1>

    <select v-model="statusFilter" @change="loadOrders">
      <option value="">Tutti gli stati</option>
      <option value="pending">In attesa</option>
      <option value="preparing">In preparazione</option>
      <option value="served">Servito</option>
    </select>

    <p v-if="!orders.length">Nessun ordine.</p>
    <OrderCard
      v-for="order in orders"
      :key="order.id"
      :order="order"
      :advancing="advancingIds.includes(order.id)"
      @advance="advance"
    />
  </AdminLayout>
</template>

<style scoped>
h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
}

select {
  display: block;
  margin: 0.5rem 0 1.5rem;
  padding: 0.4rem;
  border-radius: 0.5rem;
  border: 1px solid #ccc;
}
</style>
