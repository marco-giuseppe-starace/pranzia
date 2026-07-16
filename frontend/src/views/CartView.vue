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

// Riga nota in modifica (order.id + item.id), e valore in bozza: usati per
// mostrare l'input inline solo su una nota alla volta.
const editingItemId = ref(null)
const noteDraft = ref('')
const noteError = ref(null)
const notePlaceholder = ref('')

// Suggerisce come esempio un ingrediente vero del piatto (preso dalla sua
// descrizione), invece di un placeholder fisso sempre uguale per tutti i
// piatti: piu' utile e concreto per il cliente che vuole togliere qualcosa.
function randomIngredientPlaceholder(item) {
  const description = item.menu_item_description
  if (!description) return t('cart.notePlaceholder')

  const ingredients = description
    .replace(/\.$/, '')
    .split(/,| e /i)
    .map((part) => part.trim())
    .filter(Boolean)

  if (!ingredients.length) return t('cart.notePlaceholder')

  const pick = ingredients[Math.floor(Math.random() * ingredients.length)]
  return `${t('cart.notePlaceholderPrefix')} ${pick}`
}

async function submit() {
  if (!window.confirm(t('cart.confirmSubmit'))) return

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

function startEditingNote(item) {
  editingItemId.value = item.id
  noteDraft.value = item.notes ?? ''
  noteError.value = null
  notePlaceholder.value = randomIngredientPlaceholder(item)
}

function cancelEditingNote() {
  editingItemId.value = null
  noteError.value = null
}

async function saveNote(order, item) {
  noteError.value = null

  try {
    await api.patch(`/orders/${order.id}/items/${item.id}`, { notes: noteDraft.value })
    editingItemId.value = null
    await loadOrders()
  } catch (e) {
    noteError.value = e.message
  }
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
    <p v-if="session.tableNumber" class="table-number">{{ t('cart.table') }} {{ session.tableNumber }}</p>

    <p v-if="!cart.items.length && !orders.length">{{ t('cart.empty') }}</p>

    <ul v-if="cart.items.length" class="lines">
      <li v-for="item in cart.items" :key="item.menuItemId">
        <span class="name">{{ item.name }}</span>

        <div class="stepper">
          <button
            type="button"
            class="step"
            :aria-label="t('menu.decrease')"
            @click="cart.updateQuantity(item.menuItemId, item.quantity - 1)"
          >&minus;</button>
          <span class="qty">{{ item.quantity }}</span>
          <button
            type="button"
            class="step"
            :aria-label="t('menu.increase')"
            @click="cart.updateQuantity(item.menuItemId, item.quantity + 1)"
          >+</button>
        </div>

        <span class="price">{{ (item.price * item.quantity).toFixed(2) }} &euro;</span>
        <button type="button" class="remove" @click="cart.remove(item.menuItemId)">{{ t('cart.remove') }}</button>
      </li>
    </ul>

    <p v-if="cart.items.length" class="total">{{ t('cart.total') }}: <strong>{{ cart.total.toFixed(2) }} &euro;</strong></p>

    <p v-if="error" class="error">{{ error }}</p>

    <button v-if="cart.items.length" type="button" class="submit" :disabled="submitting" @click="submit">
      {{ submitting ? '...' : t('cart.submit') }}
    </button>

    <section v-if="orders.length" class="orders">
      <article v-for="order in orders" :key="order.id" class="order" :class="order.status">
        <header>
          <span>Ordine #{{ order.id }}</span>
          <span class="badge">{{ t(`cart.status.${order.status}`) }}</span>
        </header>
        <ul>
          <li v-for="item in order.items" :key="item.id">
            <div class="item-line">
              <span>{{ item.quantity }}x {{ item.menu_item_name }}</span>
              <button
                v-if="order.status === 'pending' && editingItemId !== item.id"
                type="button"
                class="edit-note"
                @click="startEditingNote(item)"
              >
                <span class="edit-note-icon" aria-hidden="true">&#9998;</span>
                {{ item.notes ? t('cart.editNote') : t('cart.addNote') }}
              </button>
            </div>

            <p v-if="item.notes && editingItemId !== item.id" class="notes">{{ item.notes }}</p>

            <div v-if="editingItemId === item.id" class="note-editor">
              <input v-model="noteDraft" type="text" :placeholder="notePlaceholder" />
              <button type="button" class="save-note" @click="saveNote(order, item)">{{ t('cart.save') }}</button>
              <button type="button" class="cancel-note" @click="cancelEditingNote">{{ t('cart.cancel') }}</button>
            </div>
          </li>
        </ul>
        <p v-if="noteError" class="error">{{ noteError }}</p>
        <footer>{{ Number(order.total).toFixed(2) }} &euro;</footer>
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
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.lines .name {
  flex: 1;
  font-weight: 600;
  color: #1a1a1a;
}

.stepper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.step {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 50%;
  border: none;
  background: #ef9f27;
  color: #412402;
  font-size: 1.1rem;
  font-weight: 700;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.15s ease;
  flex-shrink: 0;
}

.step:active {
  transform: scale(0.9);
}

.qty {
  min-width: 1.25rem;
  text-align: center;
  font-weight: 600;
}

.price {
  min-width: 4.5rem;
  text-align: right;
  color: #555;
}

.remove {
  background: none;
  border: 1px solid #e0d9cc;
  color: #8a7654;
  border-radius: 0.4rem;
  padding: 0.3rem 0.6rem;
  font-size: 0.8rem;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.remove:hover {
  background: #fdf1de;
}

.total {
  font-size: 1.05rem;
  margin-top: 1.25rem;
  color: #412402;
}

.total strong {
  font-size: 1.2rem;
}

.error {
  color: #d85a30;
  margin-top: 0.75rem;
}

.submit {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.65rem 1.5rem;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.15s ease, transform 0.15s ease;
}

.submit:hover:not(:disabled) {
  background: #e08f16;
}

.submit:active:not(:disabled) {
  transform: scale(0.97);
}

.submit:disabled {
  opacity: 0.7;
  cursor: default;
}

.orders {
  margin-top: 2.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.order {
  background: white;
  border: 1px solid #eee;
  border-left: 4px solid #ccc;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
}

.order.pending {
  border-left-color: #ef9f27;
}

.order.preparing {
  border-left-color: #d85a30;
}

.order.served {
  border-left-color: #6b9b5e;
  opacity: 0.75;
}

.order header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.badge {
  font-size: 0.75rem;
  font-weight: 600;
  color: #412402;
  background: #fdf1de;
  border-radius: 999px;
  padding: 0.15rem 0.6rem;
}

.order ul {
  margin: 0;
  padding-left: 1.2rem;
  color: #444;
  list-style: none;
}

.order ul li {
  margin-bottom: 0.4rem;
}

.item-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.notes {
  margin: 0.15rem 0 0;
  font-size: 0.8rem;
  color: #8a7654;
  font-style: italic;
}

.edit-note {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: #fdf1de;
  border: 1px solid #f0dcb8;
  border-radius: 999px;
  padding: 0.2rem 0.6rem 0.2rem 0.4rem;
  color: #d85a30;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background-color 0.15s ease, transform 0.15s ease;
}

.edit-note:hover {
  background: #fbe6c4;
}

.edit-note:active {
  transform: scale(0.96);
}

.edit-note-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  background: #d85a30;
  color: white;
  font-size: 0.6rem;
  flex-shrink: 0;
}

.note-editor {
  display: flex;
  gap: 0.4rem;
  margin-top: 0.3rem;
}

.note-editor input {
  flex: 1;
  padding: 0.3rem 0.5rem;
  border: 1px solid #ccc;
  border-radius: 0.4rem;
  font-size: 0.85rem;
}

.save-note,
.cancel-note {
  border: none;
  border-radius: 0.4rem;
  padding: 0.3rem 0.6rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
}

.save-note {
  background: #ef9f27;
  color: #412402;
}

.cancel-note {
  background: #f4f1ea;
  color: #412402;
}

.order footer {
  margin-top: 0.5rem;
  font-weight: 600;
  text-align: right;
}
</style>
