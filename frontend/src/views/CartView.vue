<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { api } from '../api/client.js'
import { useCartStore } from '../stores/cart.js'
import { useSessionStore } from '../stores/session.js'
import { useI18n } from '../i18n/index.js'

const cart = useCartStore()
const session = useSessionStore()
const { t } = useI18n()

const submitting = ref(false)
const error = ref(null)
const orders = ref([])
let pollTimer = null

async function submit() {
  submitting.value = true
  error.value = null

  try {
    await api.post('/orders', {
      session_id: session.sessionId,
      items: cart.items.map((item) => ({
        menu_item_id: item.menuItemId,
        quantity: item.quantity,
        notes: item.notes || undefined,
      })),
    })
    cart.clear()
    await loadOrders()
  } catch (e) {
    error.value = e.message
  } finally {
    submitting.value = false
  }
}

async function loadOrders() {
  if (!session.sessionId) return
  const response = await api.get(`/orders/${session.sessionId}`)
  orders.value = response.data
}

onMounted(() => {
  loadOrders()
  // Polling leggero per riflettere l'avanzamento in cucina (in
  // attesa/in preparazione/servito) senza bisogno di WebSocket.
  pollTimer = setInterval(loadOrders, 10_000)
})

onUnmounted(() => {
  clearInterval(pollTimer)
})
</script>

<template>
  <main class="cart">
    <h1>{{ t('cart.title') }}</h1>

    <p v-if="!cart.items.length && !orders.length">{{ t('cart.empty') }}</p>

    <ul v-if="cart.items.length" class="lines">
      <li v-for="item in cart.items" :key="item.menuItemId">
        <span class="name">{{ item.name }}</span>
        <input
          type="number"
          min="1"
          :value="item.quantity"
          @change="cart.updateQuantity(item.menuItemId, Number($event.target.value))"
        />
        <span class="price">{{ (item.price * item.quantity).toFixed(2) }} &euro;</span>
        <button type="button" @click="cart.remove(item.menuItemId)">{{ t('cart.remove') }}</button>
      </li>
    </ul>

    <p v-if="cart.items.length" class="total">{{ t('cart.total') }}: {{ cart.total.toFixed(2) }} &euro;</p>

    <p v-if="error" class="error">{{ error }}</p>

    <button v-if="cart.items.length" type="button" class="submit" :disabled="submitting" @click="submit">
      {{ t('cart.submit') }}
    </button>

    <section v-if="orders.length" class="orders">
      <article v-for="order in orders" :key="order.id" class="order">
        <header>
          #{{ order.id }} — {{ t(`cart.status.${order.status}`) }} — {{ Number(order.total).toFixed(2) }} &euro;
        </header>
        <ul>
          <li v-for="item in order.items" :key="item.id">
            {{ item.quantity }}x {{ item.menu_item_name }}
          </li>
        </ul>
      </article>
    </section>
  </main>
</template>

<style scoped>
.cart {
  max-width: 640px;
  margin: 0 auto;
  padding: 1rem 1.5rem 3rem;
  font-family: 'Inter', system-ui, sans-serif;
}

h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
}

.lines {
  list-style: none;
  padding: 0;
}

.lines li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.lines .name {
  flex: 1;
}

.lines input {
  width: 3rem;
}

.total {
  font-weight: 600;
  margin-top: 1rem;
}

.error {
  color: #d85a30;
}

.submit {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.6rem 1.25rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1rem;
}

.orders {
  margin-top: 2rem;
}

.order {
  border: 1px solid #f0f0f0;
  border-radius: 0.5rem;
  padding: 0.75rem;
  margin-bottom: 0.75rem;
}

.order header {
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.order ul {
  margin: 0;
  padding-left: 1.2rem;
}
</style>
