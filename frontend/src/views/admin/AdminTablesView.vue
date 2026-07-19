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
// Id dei tavoli con una chiusura sessione in corso: disabilita il
// relativo pulsante finche' la richiesta non e' completata.
const closingIds = ref([])
const error = ref(null)
let pollTimer = null

async function loadTables() {
  const response = await api.get('/admin/tables', opts)
  tables.value = response.data
}

async function closeSession(tableNumber, tableId) {
  if (closingIds.value.includes(tableId)) return
  const confirmed = await confirmDialog.confirm(
    `Chiudere la sessione del Tavolo ${tableNumber}? Se il cliente vuole ordinare ancora dovra' riscansionare il QR.`,
  )
  if (!confirmed) return

  error.value = null
  closingIds.value.push(tableId)

  try {
    await api.post(`/admin/tables/${tableId}/close-session`, {}, opts)
    await loadTables()
  } catch (e) {
    error.value = e.body?.message ?? 'Chiusura tavolo non riuscita.'
  } finally {
    closingIds.value = closingIds.value.filter((id) => id !== tableId)
  }
}

onMounted(() => {
  loadTables()
  // Polling per riflettere in tempo (quasi) reale quando un tavolo
  // diventa occupato (nuovo cliente scansiona il QR) o si libera,
  // senza richiedere un ricaricamento manuale della pagina.
  pollTimer = setInterval(loadTables, 5_000)
})

onUnmounted(() => clearInterval(pollTimer))
</script>

<template>
  <AdminLayout>
    <h1>Tavoli</h1>
    <p class="hint">
      Chiudi la sessione di un tavolo quando i clienti se ne vanno: cosi' la
      prossima scansione dello stesso QR apre una sessione nuova, invece di
      mostrare ai clienti successivi il carrello e lo storico ordini di chi
      era seduto prima. Un tavolo va prima incassato da "In cassa": non si
      puo' chiudere un tavolo occupato non ancora pagato.
    </p>

    <p v-if="error" class="error">{{ error }}</p>

    <ul class="tables">
      <li v-for="table in tables" :key="table.id" class="table" :class="table.status">
        <span class="number">Tavolo {{ table.number }}</span>
        <span class="status">{{ table.status === 'active' ? 'Occupato' : 'Libero' }}</span>
        <span v-if="table.status === 'active'" class="paid-badge" :class="{ paid: table.paid }">
          {{ table.paid ? 'Pagato' : 'Da incassare' }}
        </span>
        <button
          v-if="table.status === 'active'"
          type="button"
          :disabled="closingIds.includes(table.id) || !table.paid"
          :title="table.paid ? '' : 'Incassa il tavolo da \'In cassa\' prima di poterlo chiudere'"
          @click="closeSession(table.number, table.id)"
        >
          {{ closingIds.includes(table.id) ? 'Chiusura...' : 'Chiudi tavolo' }}
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
  border-left: 4px solid #6b9b5e;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
}

.table.active {
  border-left-color: #ef9f27;
}

.number {
  font-weight: 600;
  flex: 1;
}

.status {
  font-size: 0.85rem;
  color: #555;
}

.paid-badge {
  font-size: 0.72rem;
  font-weight: 600;
  border-radius: 999px;
  padding: 0.15rem 0.6rem;
  background: #fbe4dc;
  color: #a13f1e;
}

.paid-badge.paid {
  background: #e6f2e1;
  color: #3f6b31;
}

.error {
  color: #d85a30;
  font-size: 0.9rem;
  font-weight: 600;
  margin: 0 0 1rem;
}

button {
  background: #d85a30;
  color: white;
  border: none;
  border-radius: 0.4rem;
  padding: 0.35rem 0.75rem;
  font-weight: 600;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: default;
}
</style>
