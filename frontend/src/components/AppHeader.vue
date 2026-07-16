<script setup>
import { useCartStore } from '../stores/cart.js'
import { useI18n } from '../i18n/index.js'
import LanguageSwitcher from './LanguageSwitcher.vue'

const cart = useCartStore()
const { t } = useI18n()
</script>

<template>
  <header class="app-header">
    <RouterLink to="/menu" class="brand">
      <img src="/pranzia-icon-192.png" alt="Pranzia" width="32" height="32" />
      <span>PranzIA</span>
    </RouterLink>

    <nav>
      <RouterLink to="/menu">{{ t('nav.menu') }}</RouterLink>
      <RouterLink to="/chat">{{ t('nav.chat') }}</RouterLink>
      <RouterLink to="/cart" class="cart-link">
        {{ t('nav.cart') }}
        <span v-if="cart.count > 0" class="cart-badge">{{ cart.count }}</span>
      </RouterLink>
    </nav>

    <LanguageSwitcher />
  </header>
</template>

<style scoped>
.app-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem 1rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #eee;
  font-family: 'Inter', system-ui, sans-serif;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: 'Baloo 2', sans-serif;
  font-weight: 700;
  color: #412402;
  text-decoration: none;
  margin-right: auto;
}

nav {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 1rem;
}

nav a {
  color: #412402;
  text-decoration: none;
  white-space: nowrap;
}

@media (max-width: 480px) {
  .app-header {
    gap: 0.4rem 0.6rem;
    padding: 0.6rem 0.75rem;
  }

  .brand span {
    display: none;
  }

  nav {
    gap: 0.4rem 0.75rem;
    font-size: 0.9rem;
  }
}

nav a.router-link-active {
  color: #d85a30;
  font-weight: 600;
}

.cart-link {
  position: relative;
}

.cart-badge {
  background: #ef9f27;
  color: #412402;
  border-radius: 999px;
  padding: 0 0.4rem;
  font-size: 0.75rem;
  margin-left: 0.25rem;
}
</style>
