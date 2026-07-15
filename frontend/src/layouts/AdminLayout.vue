<script setup>
import { useRouter } from 'vue-router'
import { useAdminAuthStore } from '../stores/adminAuth.js'

const router = useRouter()
const adminAuth = useAdminAuthStore()

async function logout() {
  await adminAuth.logout()
  router.replace('/admin/login')
}
</script>

<template>
  <div class="admin">
    <header class="admin-header">
      <RouterLink to="/admin/orders" class="brand">PranzIA — Staff</RouterLink>
      <nav>
        <RouterLink to="/admin/orders">Ordini</RouterLink>
        <RouterLink to="/admin/menu">Menu</RouterLink>
        <RouterLink to="/admin/ai-costs">Costi IA</RouterLink>
      </nav>
      <button type="button" class="logout" @click="logout">Esci</button>
    </header>

    <main class="admin-content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.admin {
  font-family: 'Inter', system-ui, sans-serif;
  min-height: 100vh;
  background: #f9f9f9;
}

.admin-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 0.75rem 1.5rem;
  background: #412402;
  color: white;
}

.brand {
  font-family: 'Baloo 2', sans-serif;
  font-weight: 700;
  color: white;
  text-decoration: none;
  margin-right: auto;
}

nav {
  display: flex;
  gap: 1rem;
}

nav a {
  color: #f4f1ea;
  text-decoration: none;
}

nav a.router-link-active {
  color: #ef9f27;
  font-weight: 600;
}

.logout {
  background: transparent;
  border: 1px solid #ef9f27;
  color: #ef9f27;
  border-radius: 0.5rem;
  padding: 0.3rem 0.75rem;
  cursor: pointer;
}

.admin-content {
  max-width: 960px;
  margin: 0 auto;
  padding: 1.5rem;
}
</style>
