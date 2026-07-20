<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { api } from '../../api/client.js'
import { useAdminAuthStore } from '../../stores/adminAuth.js'
import { useConfirmDialogStore } from '../../stores/confirmDialog.js'
import AdminLayout from '../../layouts/AdminLayout.vue'

const adminAuth = useAdminAuthStore()
const confirmDialog = useConfirmDialogStore()
const opts = { token: adminAuth.token }

const tables = ref([])
const todayTotal = ref('0')
const todayCount = ref(0)
const error = ref(null)
// Id delle sessioni con un incasso in corso: disabilita il relativo
// pulsante finche' la richiesta non e' completata.
const payingIds = ref([])
// Stesso meccanismo per l'invio dell'anteprima del conto.
const sendingReceiptIds = ref([])
// Coperti per sessione (session_id -> numero): precompilati con quanto
// gia' inserito dal cliente prima di ordinare (vedi GuestsModal.vue lato
// cliente), lo staff puo' comunque correggerli prima di incassare.
const guestsBySession = ref({})
let pollTimer = null

function guestsFor(sessionId) {
  return guestsBySession.value[sessionId] ?? 1
}

async function load() {
  // A differenza delle altre rotte admin, questa non passa da una Laravel
  // Resource: la risposta non e' avvolta in { data: ... }.
  const response = await api.get('/admin/cash-register', opts)
  tables.value = response.tables
  todayTotal.value = response.today_total
  todayCount.value = response.today_count
  for (const table of response.tables) {
    if (!(table.session_id in guestsBySession.value)) {
      guestsBySession.value[table.session_id] = table.guests ?? 1
    }
  }
}

async function pay(table) {
  if (payingIds.value.includes(table.session_id)) return
  const guests = guestsFor(table.session_id)
  const confirmed = await confirmDialog.confirm(
    `Incassare il Tavolo ${table.number}? Totale piatti ${Number(table.total).toFixed(2)} € + coperto per ${guests} persone.`,
  )
  if (!confirmed) return

  error.value = null
  payingIds.value.push(table.session_id)
  try {
    await api.post(`/admin/cash-register/${table.session_id}/pay`, { guests }, opts)
    await load()
  } catch (e) {
    error.value = e.body?.message ?? 'Incasso non riuscito.'
  } finally {
    payingIds.value = payingIds.value.filter((id) => id !== table.session_id)
  }
}

// Rende visibile al cliente l'anteprima del conto (totale e divisione tra
// i coperti) senza incassare: utile mentre i clienti decidono come
// pagare, ad esempio dividendosi il conto "alla romana".
async function sendReceipt(table) {
  if (sendingReceiptIds.value.includes(table.session_id)) return

  error.value = null
  sendingReceiptIds.value.push(table.session_id)
  try {
    await api.post(
      `/admin/cash-register/${table.session_id}/send-receipt`,
      { guests: guestsFor(table.session_id) },
      opts,
    )
    await load()
  } catch (e) {
    error.value = e.body?.message ?? 'Invio ricevuta non riuscito.'
  } finally {
    sendingReceiptIds.value = sendingReceiptIds.value.filter((id) => id !== table.session_id)
  }
}

onMounted(() => {
  load()
  // Polling per riflettere in tempo (quasi) reale nuovi ordini/tavoli,
  // senza richiedere un ricaricamento manuale della pagina.
  pollTimer = setInterval(load, 5_000)
})

onUnmounted(() => clearInterval(pollTimer))
</script>

<template>
  <AdminLayout>
    <h1>In cassa</h1>
    <p class="hint">
      Incassa un tavolo quando i clienti pagano: il tavolo resta occupato
      finche' non lo liberi da "Tavoli" con "Chiudi tavolo" (permesso solo
      dopo l'incasso).
    </p>

    <p v-if="error" class="error">{{ error }}</p>
    <p v-if="!tables.length" class="empty">Nessun tavolo da incassare al momento.</p>

    <ul class="tables">
      <li v-for="table in tables" :key="table.session_id" class="table">
        <div class="info">
          <span class="number">Tavolo {{ table.number }}</span>
          <span class="orders">{{ table.order_count }} {{ table.order_count === 1 ? 'ordine' : 'ordini' }}</span>
        </div>
        <span class="total">{{ Number(table.total).toFixed(2) }} &euro;</span>
        <label class="guests">
          Coperti
          <input
            v-model.number="guestsBySession[table.session_id]"
            type="number"
            min="1"
            max="100"
          />
        </label>
        <button
          type="button"
          class="send-receipt"
          :disabled="sendingReceiptIds.includes(table.session_id)"
          @click="sendReceipt(table)"
        >
          {{ sendingReceiptIds.includes(table.session_id) ? 'Invio...' : (table.receipt_sent ? 'Ricevuta inviata ✓' : 'Invia ricevuta') }}
        </button>
        <button
          type="button"
          :disabled="payingIds.includes(table.session_id)"
          @click="pay(table)"
        >
          {{ payingIds.includes(table.session_id) ? 'Incasso...' : 'Incassa' }}
        </button>
      </li>
    </ul>

    <div class="totals">
      <span class="totals-label">Incassato oggi</span>
      <span class="totals-value">{{ Number(todayTotal).toFixed(2) }} &euro;</span>
      <span class="totals-count">{{ todayCount }} tavol{{ todayCount === 1 ? 'o' : 'i' }} pagat{{ todayCount === 1 ? 'o' : 'i' }}</span>
    </div>
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

.error {
  color: #d85a30;
  font-size: 0.9rem;
  font-weight: 600;
}

.tables {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.table {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: white;
  border: 1px solid #eee;
  border-left: 4px solid #ef9f27;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
}

.info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.number {
  font-weight: 600;
}

.orders {
  font-size: 0.8rem;
  color: #666;
}

.total {
  font-weight: 700;
  color: #412402;
  font-size: 1.05rem;
}

.guests {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
  font-size: 0.72rem;
  color: #666;
  font-weight: 600;
}

.guests input {
  width: 3.2rem;
  padding: 0.25rem;
  border: 1px solid #ccc;
  border-radius: 0.4rem;
  text-align: center;
  font: inherit;
}

button {
  background: #6b9b5e;
  color: white;
  border: none;
  border-radius: 0.4rem;
  padding: 0.5rem 0.9rem;
  font-weight: 600;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: default;
}

.send-receipt {
  background: #fdf1de;
  color: #412402;
  border: 1px solid #f0dcb8;
  white-space: nowrap;
}

.totals {
  margin-top: 2rem;
  padding: 1rem 1.25rem;
  background: #412402;
  border-radius: 0.75rem;
  display: flex;
  align-items: baseline;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.totals-label {
  color: #f4f1ea;
  font-weight: 600;
}

.totals-value {
  color: #ef9f27;
  font-family: 'Baloo 2', sans-serif;
  font-size: 1.6rem;
  font-weight: 700;
  margin-right: auto;
}

.totals-count {
  color: #cbb99a;
  font-size: 0.85rem;
}
</style>
