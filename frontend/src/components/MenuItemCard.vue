<script setup>
import { useCartStore } from '../stores/cart.js'
import { useI18n } from '../i18n/index.js'

const props = defineProps({
  item: { type: Object, required: true },
})

const cart = useCartStore()
const { t } = useI18n()
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
      <button v-if="item.available" type="button" @click="cart.add(item)">
        {{ t('menu.add') }}
      </button>
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

button {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.35rem 0.75rem;
  font-weight: 600;
  cursor: pointer;
}

.unavailable-label {
  font-size: 0.8rem;
  color: #999;
}
</style>
