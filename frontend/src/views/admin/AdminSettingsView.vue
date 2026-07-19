<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../../api/client.js'
import { useAdminAuthStore } from '../../stores/adminAuth.js'
import AdminLayout from '../../layouts/AdminLayout.vue'

const adminAuth = useAdminAuthStore()
const opts = { token: adminAuth.token }

const tableCount = ref(10)
const coverCharge = ref(0)
const loading = ref(true)
const saving = ref(false)
const error = ref(null)
const saved = ref(false)

async function load() {
  loading.value = true
  const response = await api.get('/admin/settings', opts)
  tableCount.value = response.table_count
  coverCharge.value = Number(response.cover_charge)
  loading.value = false
}

async function save() {
  error.value = null
  saved.value = false
  saving.value = true
  try {
    await api.put('/admin/settings', {
      table_count: Number(tableCount.value),
      cover_charge: Number(coverCharge.value),
    }, opts)
    saved.value = true
    setTimeout(() => { saved.value = false }, 2000)
  } catch (e) {
    error.value = e.body?.message ?? 'Salvataggio non riuscito.'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <AdminLayout>
    <h1>Impostazioni</h1>

    <form v-if="!loading" class="settings-form" @submit.prevent="save">
      <label>
        Numero di tavoli
        <input v-model="tableCount" type="number" min="1" max="200" required />
      </label>
      <p class="field-hint">
        Al momento e' solo un valore salvato: non crea o rimuove ancora i
        tavoli veri e propri.
      </p>

      <label>
        Prezzo del coperto (&euro;)
        <input v-model="coverCharge" type="number" min="0" step="0.01" required />
      </label>

      <p v-if="error" class="error">{{ error }}</p>
      <p v-if="saved" class="success">Impostazioni salvate.</p>

      <button type="submit" :disabled="saving">{{ saving ? 'Salvataggio...' : 'Salva' }}</button>
    </form>
  </AdminLayout>
</template>

<style scoped>
h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  max-width: 20rem;
  background: white;
  border: 1px solid #f0f0f0;
  border-radius: 0.75rem;
  padding: 1.25rem;
  margin-top: 1rem;
  box-shadow: 0 1px 3px rgba(65, 36, 2, 0.06);
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #412402;
  margin-top: 0.75rem;
}

label:first-child {
  margin-top: 0;
}

input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 0.4rem;
  font: inherit;
  font-weight: 400;
}

.field-hint {
  margin: 0;
  color: #666;
  font-size: 0.78rem;
}

.error {
  color: #d85a30;
  font-size: 0.85rem;
  font-weight: 600;
  margin: 0.75rem 0 0;
}

.success {
  color: #3f6b31;
  font-size: 0.85rem;
  font-weight: 600;
  margin: 0.75rem 0 0;
}

button {
  margin-top: 1rem;
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.55rem 1rem;
  font-weight: 600;
  cursor: pointer;
  align-self: flex-start;
}

button:disabled {
  opacity: 0.6;
  cursor: default;
}
</style>
