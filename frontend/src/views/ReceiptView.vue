<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../api/client.js'
import { useSessionStore } from '../stores/session.js'
import { useI18n } from '../i18n/index.js'

const session = useSessionStore()
const { t } = useI18n()

const receipt = ref(null)
const error = ref(null)
const email = ref('')
const sending = ref(false)
const sent = ref(false)
const emailError = ref(null)

async function load() {
  error.value = null
  try {
    receipt.value = await api.get(`/sessions/${session.sessionId}/receipt`)
  } catch (e) {
    error.value = e.body?.message ?? e.message
  }
}

function downloadUrl() {
  const base = import.meta.env.VITE_API_URL ?? '/api'
  return `${base}/sessions/${session.sessionId}/receipt/pdf`
}

async function sendEmail() {
  emailError.value = null
  sent.value = false
  sending.value = true
  try {
    await api.post(`/sessions/${session.sessionId}/receipt/email`, { email: email.value })
    sent.value = true
  } catch (e) {
    emailError.value = e.body?.message ?? t('receipt.emailError')
  } finally {
    sending.value = false
  }
}

onMounted(load)
</script>

<template>
  <main class="receipt">
    <h1>{{ t('receipt.title') }}</h1>

    <p v-if="error" class="error">{{ error }}</p>

    <template v-if="receipt">
      <p class="table-number">{{ t('cart.table') }} {{ receipt.table_number }}</p>

      <ul class="lines">
        <li v-for="(item, index) in receipt.items" :key="index">
          <span class="name">{{ item.quantity }}x {{ item.name }}</span>
          <span class="price">{{ item.total.toFixed(2) }} &euro;</span>
        </li>
      </ul>

      <div class="totals">
        <div class="row">
          <span>{{ t('receipt.itemsSubtotal') }}</span>
          <span>{{ receipt.items_subtotal.toFixed(2) }} &euro;</span>
        </div>
        <div v-if="receipt.cover_total > 0" class="row">
          <span>{{ t('receipt.coverCharge') }} ({{ receipt.guests }} &times; {{ receipt.cover_charge.toFixed(2) }} &euro;)</span>
          <span>{{ receipt.cover_total.toFixed(2) }} &euro;</span>
        </div>
        <div class="row total">
          <span>{{ t('cart.total') }}</span>
          <span>{{ receipt.total.toFixed(2) }} &euro;</span>
        </div>
      </div>

      <a class="download" :href="downloadUrl()" target="_blank" rel="noopener">{{ t('receipt.downloadPdf') }}</a>

      <form class="email-form" @submit.prevent="sendEmail">
        <label>
          {{ t('receipt.emailLabel') }}
          <input v-model="email" type="email" required :placeholder="t('receipt.emailPlaceholder')" />
        </label>
        <button type="submit" :disabled="sending">{{ sending ? '...' : t('receipt.sendEmail') }}</button>
        <p v-if="sent" class="success">{{ t('receipt.emailSent') }}</p>
        <p v-if="emailError" class="error">{{ emailError }}</p>
      </form>
    </template>
  </main>
</template>

<style scoped>
.receipt {
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

.table-number {
  color: #d85a30;
  font-weight: 600;
  margin-bottom: 1rem;
}

.lines {
  list-style: none;
  padding: 0;
  margin: 0;
}

.lines li {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.lines .name {
  color: #1a1a1a;
}

.lines .price {
  color: #555;
}

.totals {
  margin-top: 1rem;
  padding-top: 0.5rem;
}

.row {
  display: flex;
  justify-content: space-between;
  padding: 0.25rem 0;
  color: #555;
}

.row.total {
  border-top: 2px solid #412402;
  margin-top: 0.4rem;
  padding-top: 0.5rem;
  font-weight: 700;
  font-size: 1.1rem;
  color: #412402;
}

.download {
  display: inline-block;
  margin-top: 1.5rem;
  background: #412402;
  color: white;
  text-decoration: none;
  border-radius: 0.5rem;
  padding: 0.6rem 1.2rem;
  font-weight: 600;
}

.email-form {
  margin-top: 1.5rem;
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 0.6rem;
}

.email-form label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: #412402;
  flex: 1 1 14rem;
}

.email-form input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 0.4rem;
  font: inherit;
}

.email-form button {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.55rem 1rem;
  font-weight: 600;
  cursor: pointer;
}

.email-form button:disabled {
  opacity: 0.6;
  cursor: default;
}

.success {
  color: #3f6b31;
  font-weight: 600;
  width: 100%;
  margin: 0.4rem 0 0;
}

.error {
  color: #d85a30;
  font-weight: 600;
  width: 100%;
  margin: 0.4rem 0 0;
}
</style>
