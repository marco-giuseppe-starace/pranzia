<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { api } from '../../api/client.js'
import { useAdminAuthStore } from '../../stores/adminAuth.js'
import AdminLayout from '../../layouts/AdminLayout.vue'

const adminAuth = useAdminAuthStore()
const opts = { token: adminAuth.token }

const TYPE_LABELS = {
  glass: 'Un bicchiere',
  salt: 'Sale',
  oil: 'Olio',
  napkins: 'Tovaglioli',
  cutlery: 'Posate',
  bill: 'Il conto',
  other: 'Altro',
}

const TYPE_ICONS = {
  glass: '🥛',
  salt: '🧂',
  oil: '🫒',
  napkins: '🧻',
  cutlery: '🍴',
  bill: '🧾',
  other: '✋',
}

const requests = ref([])
// Id delle richieste con una risoluzione in corso: disabilita il relativo
// pulsante finche' la richiesta non e' completata.
const resolvingIds = ref([])
let pollTimer = null

function label(request) {
  return request.type === 'other' && request.note ? request.note : TYPE_LABELS[request.type]
}

async function load() {
  const response = await api.get('/admin/service-requests', opts)
  requests.value = response.data
}

async function resolve(id) {
  if (resolvingIds.value.includes(id)) return
  resolvingIds.value.push(id)
  try {
    await api.patch(`/admin/service-requests/${id}/resolve`, {}, opts)
    await load()
  } finally {
    resolvingIds.value = resolvingIds.value.filter((i) => i !== id)
  }
}

onMounted(() => {
  load()
  // Polling frequente: sono richieste che il cliente si aspetta vengano
  // notate quasi subito, come chiamare il cameriere.
  pollTimer = setInterval(load, 5_000)
})

onUnmounted(() => clearInterval(pollTimer))
</script>

<template>
  <AdminLayout>
    <h1>Richieste</h1>
    <p class="hint">
      "Mi può portare..." del cliente: acqua, posate, il conto, ecc. Segna
      "Fatto" appena la richiesta è stata gestita.
    </p>

    <p v-if="!requests.length" class="empty">Nessuna richiesta al momento.</p>

    <ul class="requests">
      <li v-for="request in requests" :key="request.id" class="request">
        <span class="icon" aria-hidden="true">{{ TYPE_ICONS[request.type] }}</span>
        <div class="info">
          <span class="number">Tavolo {{ request.table_number }}</span>
          <span class="label">{{ label(request) }}</span>
        </div>
        <button
          type="button"
          :disabled="resolvingIds.includes(request.id)"
          @click="resolve(request.id)"
        >
          {{ resolvingIds.includes(request.id) ? 'Un attimo...' : 'Fatto' }}
        </button>
      </li>
    </ul>
  </AdminLayout>
</template>

<style scoped>
h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
}

.hint {
  color: #666;
  font-size: 0.9rem;
  max-width: 40rem;
  margin: 0.25rem 0 1.5rem;
}

.empty {
  color: #666;
  font-size: 0.9rem;
}

.requests {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.request {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: white;
  border: 1px solid #eee;
  border-left: 4px solid #ef9f27;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
}

.icon {
  font-size: 1.6rem;
  flex-shrink: 0;
}

.info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.number {
  font-weight: 600;
  color: #412402;
}

.label {
  font-size: 0.9rem;
  color: #555;
}

button {
  background: #6b9b5e;
  color: white;
  border: none;
  border-radius: 0.4rem;
  padding: 0.4rem 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: default;
}
</style>
