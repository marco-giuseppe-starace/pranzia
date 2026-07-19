<script setup>
import { computed, ref } from 'vue'
import { useCartStore } from '../stores/cart.js'
import { useI18n } from '../i18n/index.js'
import DishAssistant from './DishAssistant.vue'

const props = defineProps({
  item: { type: Object, required: true },
})

const cart = useCartStore()
const { t } = useI18n()

const showAssistant = ref(false)

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
    <div class="thumb">
      <img v-if="item.image_url" :src="item.image_url" :alt="item.name" />
      <span v-else class="thumb-placeholder" aria-hidden="true">{{ item.name.charAt(0) }}</span>
    </div>

    <div class="info">
      <h3>{{ item.name }}</h3>
      <p v-if="item.description" class="description">{{ item.description }}</p>
      <ul v-if="item.allergens.length" class="allergens">
        <li v-for="allergen in item.allergens" :key="allergen.id">{{ allergen.name }}</li>
      </ul>
    </div>

    <button type="button" class="ask-ai" @click="showAssistant = true">
      <span class="ask-ai-icon" aria-hidden="true">?</span>
      {{ t('dishAssistant.openButton') }}
    </button>

    <Teleport to="body">
      <DishAssistant v-if="showAssistant" :item="item" @close="showAssistant = false" />
    </Teleport>

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
      </template>
      <span v-else class="unavailable-label">{{ t('menu.unavailable') }}</span>
    </div>

    <span v-if="justAdded" class="added-hint">{{ t('menu.added') }}</span>
  </article>
</template>

<style scoped>
.menu-item {
  display: flex;
  flex-direction: column;
  flex: 0 0 auto;
  width: 11.5rem;
  scroll-snap-align: start;
  background: white;
  border: 1px solid #f0f0f0;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(65, 36, 2, 0.06);
  font-family: 'Inter', system-ui, sans-serif;
  position: relative;
}

.menu-item.unavailable {
  opacity: 0.5;
}

.thumb {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  flex-shrink: 0;
  background: linear-gradient(135deg, #fdf1de, #f6d9a8);
  padding: 0.2rem;
  box-sizing: border-box;
  overflow: hidden;
}

.thumb::before {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='110'%20height='55'%3E%3Ctext%20x='-6'%20y='34'%20transform='rotate(-18%2055%2027)'%20font-family='sans-serif'%20font-weight='700'%20font-size='14'%20fill='rgba(65,36,2,0.16)'%3EPranzIA%3C/text%3E%3C/svg%3E");
  background-repeat: repeat;
}

.thumb img {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.thumb-placeholder {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Baloo 2', sans-serif;
  font-size: 2rem;
  font-weight: 700;
  color: #d85a30;
}

.info {
  padding: 0.6rem 0.7rem 0;
  flex: 1;
}

h3 {
  margin: 0 0 0.25rem;
  color: #1a1a1a;
  font-size: 0.95rem;
  line-height: 1.25;
}

.description {
  margin: 0 0 0.4rem;
  color: #555;
  font-size: 0.78rem;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.allergens {
  list-style: none;
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  padding: 0;
  margin: 0 0 0.4rem;
}

.allergens li {
  font-size: 0.7rem;
  color: #412402;
  background: #fdf1de;
  border-radius: 999px;
  padding: 0.1rem 0.5rem;
}

.ask-ai {
  align-self: flex-start;
  margin: 0.1rem 0.7rem 0.5rem;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: #fdf1de;
  border: 1px solid #f0dcb8;
  border-radius: 999px;
  padding: 0.2rem 0.55rem 0.2rem 0.35rem;
  color: #d85a30;
  font-size: 0.68rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background-color 0.15s ease, transform 0.15s ease;
}

.ask-ai:hover {
  background: #fbe6c4;
}

.ask-ai:active {
  transform: scale(0.96);
}

.ask-ai-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.1rem;
  height: 1.1rem;
  border-radius: 50%;
  background: #d85a30;
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  flex-shrink: 0;
}

.action {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0 0.7rem 0.7rem;
}

.price {
  font-weight: 600;
  font-size: 0.9rem;
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
  position: absolute;
  top: 0.4rem;
  left: 0.4rem;
  background: #2e7d32;
  color: white;
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
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
