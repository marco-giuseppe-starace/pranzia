<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminAuthStore } from '../../stores/adminAuth.js'

const router = useRouter()
const adminAuth = useAdminAuthStore()

const email = ref('')
const password = ref('')
const error = ref(null)
const submitting = ref(false)

async function submit() {
  error.value = null
  submitting.value = true

  try {
    await adminAuth.login(email.value, password.value)
    router.replace('/admin/orders')
  } catch (e) {
    error.value = 'Credenziali non valide.'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <main class="login">
    <form @submit.prevent="submit">
      <h1>PranzIA — Accesso staff</h1>
      <label>
        Email
        <input v-model="email" type="email" required autocomplete="username" />
      </label>
      <label>
        Password
        <input v-model="password" type="password" required autocomplete="current-password" />
      </label>
      <p v-if="error" class="error">{{ error }}</p>
      <button type="submit" :disabled="submitting">Accedi</button>
    </form>
  </main>
</template>

<style scoped>
.login {
  display: flex;
  justify-content: center;
  padding: 4rem 1.5rem;
  font-family: 'Inter', system-ui, sans-serif;
}

form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
  max-width: 320px;
}

h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
  font-size: 1.4rem;
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.9rem;
  color: #412402;
}

input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 0.5rem;
}

button {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.6rem;
  font-weight: 600;
  cursor: pointer;
}

.error {
  color: #d85a30;
  font-size: 0.9rem;
}
</style>
