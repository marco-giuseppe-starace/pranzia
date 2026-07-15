<script setup>
import { ref, onMounted, watch } from 'vue'
import { api } from '../api/client.js'
import { useI18n } from '../i18n/index.js'
import MenuItemCard from '../components/MenuItemCard.vue'
import AllergenFilter from '../components/AllergenFilter.vue'

const { t } = useI18n()
const categories = ref([])
const excludedAllergenIds = ref([])
const loading = ref(true)

// Calcolato una sola volta dal menu NON filtrato: se derivasse da `categories`
// (gia' filtrato), un allergene escluso sparirebbe dalla lista dei filtri
// stessi non appena nasconde tutti i piatti che lo contengono, rendendo
// impossibile deselezionarlo.
const allAllergens = ref([])

function extractAllergens(fullCategories) {
  const byId = new Map()
  for (const category of fullCategories) {
    for (const item of category.menu_items) {
      for (const allergen of item.allergens) {
        byId.set(allergen.id, allergen)
      }
    }
  }
  return [...byId.values()].sort((a, b) => a.name.localeCompare(b.name))
}

async function loadMenu() {
  loading.value = true
  const query = excludedAllergenIds.value.length
    ? `?exclude_allergens=${excludedAllergenIds.value.join(',')}`
    : ''
  const response = await api.get(`/menu${query}`)
  categories.value = response.data
  if (!excludedAllergenIds.value.length) {
    allAllergens.value = extractAllergens(response.data)
  }
  loading.value = false
}

onMounted(loadMenu)
watch(excludedAllergenIds, loadMenu, { deep: true })
</script>

<template>
  <main class="menu">
    <h1>{{ t('menu.title') }}</h1>

    <AllergenFilter v-model="excludedAllergenIds" :allergens="allAllergens" />

    <p v-if="loading">...</p>
    <section v-for="category in categories" :key="category.id" class="category">
      <h2>{{ category.name }}</h2>
      <MenuItemCard v-for="item in category.menu_items" :key="item.id" :item="item" />
    </section>
  </main>
</template>

<style scoped>
.menu {
  max-width: 640px;
  margin: 0 auto;
  padding: 1rem 1.5rem 3rem;
  font-family: 'Inter', system-ui, sans-serif;
}

h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
}

.category h2 {
  color: #d85a30;
  font-size: 1.1rem;
  margin-top: 1.5rem;
}
</style>
