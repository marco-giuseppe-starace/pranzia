<script setup>
const props = defineProps({
  order: { type: Object, required: true },
  advancing: { type: Boolean, default: false },
})

const emit = defineEmits(['advance'])

const STATUS_LABELS = { pending: 'In attesa', preparing: 'In preparazione', served: 'Servito' }
const NEXT_STATUS = { pending: 'preparing', preparing: 'served' }

function nextStatus() {
  return NEXT_STATUS[props.order.status]
}
</script>

<template>
  <article class="order-card" :class="order.status">
    <header>
      <span>#{{ order.id }} — Tavolo sessione {{ order.session_id }}</span>
      <span class="status">{{ STATUS_LABELS[order.status] ?? order.status }}</span>
    </header>

    <ul>
      <li v-for="item in order.items" :key="item.id">
        {{ item.quantity }}x {{ item.menu_item_name }}
        <span v-if="item.notes" class="notes">({{ item.notes }})</span>
      </li>
    </ul>

    <footer>
      <span class="total">{{ Number(order.total).toFixed(2) }} &euro;</span>
      <button
        v-if="nextStatus()"
        type="button"
        :disabled="advancing"
        @click="emit('advance', order.id, nextStatus())"
      >
        {{ advancing ? 'Aggiornamento...' : `Segna come "${STATUS_LABELS[nextStatus()]}"` }}
      </button>
    </footer>
  </article>
</template>

<style scoped>
.order-card {
  background: white;
  border: 1px solid #eee;
  border-left: 4px solid #ccc;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
  margin-bottom: 0.75rem;
}

.order-card.pending {
  border-left-color: #ef9f27;
}

.order-card.preparing {
  border-left-color: #d85a30;
}

.order-card.served {
  border-left-color: #6b9b5e;
  opacity: 0.7;
}

header {
  display: flex;
  justify-content: space-between;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.status {
  color: #412402;
}

ul {
  margin: 0 0 0.5rem;
  padding-left: 1.2rem;
}

.notes {
  color: #777;
  font-size: 0.85rem;
}

footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total {
  font-weight: 600;
}

button {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.35rem 0.75rem;
  font-weight: 600;
  cursor: pointer;
}

button:disabled {
  background: #f4e2c2;
  color: #8a7654;
  cursor: default;
}
</style>
