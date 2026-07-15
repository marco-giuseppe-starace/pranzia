<script setup>
import { computed, ref } from 'vue'
import { useCartStore } from '../stores/cart.js'
import { useI18n } from '../i18n/index.js'

const props = defineProps({
  item: { type: Object, required: true },
})

const cart = useCartStore()
const { t } = useI18n()

// Quantita' gia' presente nel carrello per questo piatto: pilota se
// mostrare il pulsante "Aggiungi" o lo stepper +/-, cosi' il cliente vede
// subito, direttamente sulla card, cosa e quanto ha gia' scelto.
const quantityInCart = computed(() => {
  const line = cart.items.find((line) => line.menuItemId === props.item.id)
  return line?.quantity ?? 0
})

// Messaggio di conferma "Aggiunto al carrello", mostrato per un istante
// dopo il click cosi' l'azione non resta silenziosa.
const justAdded = ref(false)

function handleAdd() {
  cart.add(props.item)
  justAdded.value = true
  setTimeout(() => {
    justAdded.value = false
  }, 1400)
}

function increment() {
  cart.add(props.item)
}

function decrement() {
  cart.updateQuantity(props.item.id, quantityInCart.value - 1)
}
</script>

<template>
  <article class="menu-item" :class="{ unavailable: !item.available }">
    <div class="info">
      <h3>{{ item.name }}</h3>
      <p v-if="item.description" class="description">{{ item.description }}</p>
      <ul v-if="item.allergens.length" class="allergens">
        <li v-for="allergen in item.allergens" :key="allergen.id">{{ allergen.name }}</li>
      </ul>
    </div>

    <div class="action">
      <span class="price">{{ Number(item.price).toFixed(2) }} &euro;</span>

      <template v-if="item.available">
        <button v-if="!quantityInCart" type="button" class="add" @click="handleAdd">
          {{ t('menu.add') }}
        </button>

        <div v-else class="stepper">
          <button type="button" class="step" @click="decrement" :aria-label="t('menu.decrease')">&minus;</button>
          <span class="qty">{{ quantityInCart }}</span>
          <button type="button" class="step" @click="increment" :aria-label="t('menu.increase')">+</button>
        </div>

        <span v-if="justAdded" class="added-hint">{{ t('menu.added') }}</span>
      </template>
      <span v-else class="unavailable-label">{{ t('menu.unavailable') }}</span>
    </div>
  </article>
</template>

<style scoped>
.menu-item {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f0f0f0;
  font-family: 'Inter', system-ui, sans-serif;
}

.menu-item.unavailable {
  opacity: 0.5;
}

h3 {
  margin: 0 0 0.25rem;
  color: #1a1a1a;
}

.description {
  margin: 0 0 0.25rem;
  color: #555;
  font-size: 0.9rem;
}

.allergens {
  list-style: none;
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  padding: 0;
  margin: 0;
}

.allergens li {
  font-size: 0.7rem;
  color: #412402;
  background: #fdf1de;
  border-radius: 999px;
  padding: 0.1rem 0.5rem;
}

.action {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
  flex-shrink: 0;
}

.price {
  font-weight: 600;
}

.add {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.35rem 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.add:hover {
  background: #e08f16;
}

.add:active {
  transform: scale(0.94);
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
}

.step:active {
  transform: scale(0.9);
}

.qty {
  min-width: 1.25rem;
  text-align: center;
  font-weight: 600;
}

.added-hint {
  font-size: 0.75rem;
  color: #2e7d32;
  font-weight: 600;
  animation: fade-in-out 1.4s ease;
}

@keyframes fade-in-out {
  0% {
    opacity: 0;
    transform: translateY(-0.15rem);
  }
  15%,
  70% {
    opacity: 1;
    transform: translateY(0);
  }
  100% {
    opacity: 0;
  }
}

.unavailable-label {
  font-size: 0.8rem;
  color: #999;
}
</style>
