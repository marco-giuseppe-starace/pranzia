<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../../api/client.js'
import { useAdminAuthStore } from '../../stores/adminAuth.js'
import AdminLayout from '../../layouts/AdminLayout.vue'

const adminAuth = useAdminAuthStore()
const rows = ref([])

onMounted(async () => {
  const response = await api.get('/admin/ai-costs', { token: adminAuth.token })
  rows.value = response.data
})

const TYPE_LABELS = { recommendation: 'Consigli', translation: 'Traduzione/domande' }
</script>

<template>
  <AdminLayout>
    <h1>Costi IA</h1>

    <p v-if="!rows.length">Nessuna interazione IA registrata.</p>

    <table v-else>
      <thead>
        <tr>
          <th>Mese</th>
          <th>Tipo</th>
          <th>Interazioni</th>
          <th>Token input</th>
          <th>Token output</th>
          <th>Costo stimato</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in rows" :key="`${row.month}-${row.type}`">
          <td>{{ row.month }}</td>
          <td>{{ TYPE_LABELS[row.type] ?? row.type }}</td>
          <td>{{ row.interactions }}</td>
          <td>{{ row.tokens_input }}</td>
          <td>{{ row.tokens_output }}</td>
          <td>${{ Number(row.cost_estimate).toFixed(4) }}</td>
        </tr>
      </tbody>
    </table>
  </AdminLayout>
</template>

<style scoped>
h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

th, td {
  text-align: left;
  padding: 0.4rem 0.5rem;
  border-bottom: 1px solid #eee;
  font-size: 0.9rem;
}
</style>
