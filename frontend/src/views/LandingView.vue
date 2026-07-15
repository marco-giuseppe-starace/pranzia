<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '../api/client.js'
import { useSessionStore } from '../stores/session.js'
import { useI18n } from '../i18n/index.js'

const route = useRoute()
const router = useRouter()
const session = useSessionStore()
const { t } = useI18n()

const error = ref(null)

onMounted(async () => {
  try {
    const response = await api.post('/session', { qr_token: route.params.qrToken })
    session.setSession(response.data)
    router.replace('/menu')
  } catch (e) {
    error.value = e.status === 404 ? t('landing.error') : e.message
  }
})
</script>

<template>
  <main class="landing">
    <img src="/pranzia-icon-512.png" alt="Pranzia" width="72" height="72" />
    <p v-if="!error">{{ t('landing.loading') }}</p>
    <p v-else class="error">{{ error }}</p>
  </main>
</template>

<style scoped>
.landing {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 3rem 1.5rem;
  font-family: 'Inter', system-ui, sans-serif;
  text-align: center;
}

.error {
  color: #d85a30;
}
</style>
