<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { api } from '../api/client.js'
import { useSessionStore } from '../stores/session.js'
import { useI18n } from '../i18n/index.js'

const session = useSessionStore()
const { t } = useI18n()

// Tipi comuni con un tap solo: partono subito, senza un passaggio di
// conferma in piu' — devono essere velocissimi da usare, e' il punto
// della funzione ("Mi puo' portare...").
const QUICK_TYPES = [
  { type: 'glass', icon: '🥛' },
  { type: 'salt', icon: '🧂' },
  { type: 'oil', icon: '🫒' },
  { type: 'napkins', icon: '🧻' },
  { type: 'cutlery', icon: '🍴' },
  { type: 'bill', icon: '🧾' },
]

const requests = ref([])
const sendingType = ref(null)
const error = ref(null)
const otherNote = ref('')
const sendingOther = ref(false)
let pollTimer = null

async function loadRequests() {
  if (!session.sessionId) return
  const response = await api.get(`/service-requests/${session.sessionId}`)
  requests.value = response.data
}

async function sendQuick(type) {
  if (sendingType.value) return
  error.value = null
  sendingType.value = type
  try {
    await api.post('/service-requests', { session_id: session.sessionId, type })
    await loadRequests()
  } catch (e) {
    error.value = e.body?.message ?? e.message
  } finally {
    sendingType.value = null
  }
}

async function sendOther() {
  if (!otherNote.value.trim()) return
  error.value = null
  sendingOther.value = true
  try {
    await api.post('/service-requests', { session_id: session.sessionId, type: 'other', note: otherNote.value.trim() })
    otherNote.value = ''
    await loadRequests()
  } catch (e) {
    error.value = e.body?.message ?? e.message
  } finally {
    sendingOther.value = false
  }
}

function requestLabel(request) {
  return request.type === 'other' ? request.note : t(`request.types.${request.type}`)
}

onMounted(() => {
  loadRequests()
  // Cosi' il cliente vede da solo quando lo staff ha "fatto" la
  // richiesta, senza dover ricaricare la pagina.
  pollTimer = setInterval(loadRequests, 10_000)
})

onUnmounted(() => clearInterval(pollTimer))
</script>

<template>
  <main class="request">
    <h1>{{ t('request.title') }}</h1>
    <p class="hint">{{ t('request.hint') }}</p>

    <div class="quick-grid">
      <button
        v-for="item in QUICK_TYPES"
        :key="item.type"
        type="button"
        class="quick-button"
        :disabled="sendingType === item.type"
        @click="sendQuick(item.type)"
      >
        <span class="icon" aria-hidden="true">{{ item.icon }}</span>
        {{ t(`request.types.${item.type}`) }}
      </button>
    </div>

    <form class="other-form" @submit.prevent="sendOther">
      <label>
        {{ t('request.otherLabel') }}
        <input v-model="otherNote" type="text" :placeholder="t('request.otherPlaceholder')" />
      </label>
      <button type="submit" :disabled="sendingOther || !otherNote.trim()">{{ t('request.send') }}</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>

    <section v-if="requests.length" class="log">
      <h2>{{ t('request.yourRequests') }}</h2>
      <ul>
        <li v-for="r in requests" :key="r.id" :class="r.status">
          <span>{{ requestLabel(r) }}</span>
          <span class="status">{{ r.status === 'pending' ? t('request.pending') : t('request.done') }}</span>
        </li>
      </ul>
    </section>
  </main>
</template>

<style scoped>
.request {
  max-width: 640px;
  margin: 0 auto;
  padding: 1rem 1.5rem 3rem;
  font-family: 'Inter', system-ui, sans-serif;
}

h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
  margin-bottom: 0.25rem;
}

.hint {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 1.25rem;
}

.quick-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(6.5rem, 1fr));
  gap: 0.6rem;
}

.quick-button {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.3rem;
  background: white;
  border: 1px solid #f0f0f0;
  border-radius: 0.75rem;
  padding: 0.9rem 0.5rem;
  font-size: 0.82rem;
  font-weight: 600;
  color: #412402;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(65, 36, 2, 0.06);
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.quick-button:hover:not(:disabled) {
  background: #fdf1de;
}

.quick-button:active:not(:disabled) {
  transform: scale(0.95);
}

.quick-button:disabled {
  opacity: 0.6;
}

.icon {
  font-size: 1.6rem;
}

.other-form {
  margin-top: 1.5rem;
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 0.6rem;
}

.other-form label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: #412402;
  flex: 1 1 14rem;
}

.other-form input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 0.4rem;
  font: inherit;
}

.other-form button {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.55rem 1rem;
  font-weight: 600;
  cursor: pointer;
}

.other-form button:disabled {
  opacity: 0.6;
  cursor: default;
}

.error {
  color: #d85a30;
  font-weight: 600;
  margin-top: 0.75rem;
}

.log {
  margin-top: 2rem;
}

.log h2 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
  font-size: 1.05rem;
  margin-bottom: 0.6rem;
}

.log ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.log li {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  background: white;
  border: 1px solid #f0f0f0;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.85rem;
}

.status {
  font-weight: 600;
}

.log li.pending .status {
  color: #d85a30;
}

.log li.done .status {
  color: #3f6b31;
}
</style>
