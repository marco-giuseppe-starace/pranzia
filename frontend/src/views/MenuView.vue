<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { api } from '../api/client.js'
import { useI18n } from '../i18n/index.js'
import MenuItemCard from '../components/MenuItemCard.vue'
import AllergenFilter from '../components/AllergenFilter.vue'

const { t } = useI18n()
const categories = ref([])
const excludedAllergenIds = ref([])
const loading = ref(true)

// Le 3 macro-sezioni verticali del menu, in quest'ordine fisso: dentro
// ognuna, le categorie restano scorrimento orizzontale (vedi .row) ma sono
// impilate in verticale una sopra l'altra invece che tutte in un unico blocco.
const GROUPS = ['food', 'drink', 'dessert']

const groupedCategories = computed(() =>
  GROUPS
    .map((group) => ({ group, categories: categories.value.filter((category) => category.group === group) }))
    .filter(({ categories }) => categories.length > 0)
)

// Categoria selezionata per ogni sezione (es. { food: 3, drink: 7 }): il
// tab attivo dentro ciascuna sezione, di cui si vedono i piatti sotto.
const selectedCategoryId = ref({})

function selectCategory(group, categoryId) {
  selectedCategoryId.value[group] = categoryId
}

function selectedCategory(group) {
  return group.categories.find((category) => category.id === selectedCategoryId.value[group.group]) ?? group.categories[0]
}

// Se il tab selezionato di una sezione non esiste piu' (prima apertura, o
// la categoria e' stata eliminata dall'admin), riparte dalla prima.
watch(groupedCategories, (groups) => {
  for (const group of groups) {
    const stillExists = group.categories.some((category) => category.id === selectedCategoryId.value[group.group])
    if (!stillExists) {
      selectedCategoryId.value[group.group] = group.categories[0]?.id
    }
  }
}, { immediate: true })

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
    <section v-for="group in groupedCategories" :key="group.group" class="group">
      <h2 class="group-title">{{ t(`menu.groups.${group.group}`) }}</h2>

      <div class="tabs">
        <button
          v-for="category in group.categories"
          :key="category.id"
          type="button"
          class="tab"
          :class="{ active: selectedCategory(group)?.id === category.id }"
          @click="selectCategory(group.group, category.id)"
        >{{ category.name }}</button>
      </div>

      <div class="row">
        <MenuItemCard
          v-for="item in selectedCategory(group)?.menu_items ?? []"
          :key="item.id"
          :item="item"
        />
      </div>
    </section>
  </main>
</template>

<style scoped>
.menu {
  max-width: 100%;
  margin: 0 auto;
  padding: 1rem 0 3rem;
  font-family: 'Inter', system-ui, sans-serif;
}

h1,
.group-title,
:deep(.allergen-filter) {
  padding-left: 1.5rem;
  padding-right: 1.5rem;
}

h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
}

.group {
  margin-top: 2rem;
}

.group:first-of-type {
  margin-top: 0;
}

.group-title {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
  font-size: 1.4rem;
  border-bottom: 2px solid #fdf1de;
  padding-bottom: 0.5rem;
  margin-bottom: 0;
}

/* Tab delle categorie (Antipasti/Primi/Secondi, ecc.) di una sezione:
   scorrono in orizzontale sulla stessa riga, una e' sempre selezionata e
   determina quali piatti si vedono nella riga sotto. */
.tabs {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding: 0.9rem 1.5rem 0.6rem;
  -webkit-overflow-scrolling: touch;
}

.tabs::-webkit-scrollbar {
  height: 0;
}

.tab {
  flex-shrink: 0;
  background: #f4f1ea;
  color: #412402;
  border: none;
  border-radius: 999px;
  padding: 0.4rem 0.9rem;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background-color 0.15s ease, color 0.15s ease;
}

.tab.active {
  background: #d85a30;
  color: white;
}

/* Scorrimento orizzontale dei piatti della categoria selezionata, come le
   righe di Glovo: le card hanno larghezza fissa (vedi MenuItemCard) e si
   scorrono con lo swipe invece di impilarsi in verticale. */
.row {
  display: flex;
  gap: 0.9rem;
  overflow-x: auto;
  scroll-snap-type: x proximity;
  padding: 0.1rem 1.5rem 0.75rem;
  -webkit-overflow-scrolling: touch;
}

.row::-webkit-scrollbar {
  height: 0;
}
</style>
