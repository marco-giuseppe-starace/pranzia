<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '../../api/client.js'
import { useAdminAuthStore } from '../../stores/adminAuth.js'
import AdminLayout from '../../layouts/AdminLayout.vue'
import CategoryRow from '../../components/admin/CategoryRow.vue'
import MenuItemRow from '../../components/admin/MenuItemRow.vue'

const adminAuth = useAdminAuthStore()
const opts = { token: adminAuth.token }

const categories = ref([])
const items = ref([])
const allergens = ref([])
const error = ref(null)

const newCategoryName = ref('')
const newCategoryGroup = ref('food')
const newItem = ref({ name: '', price: '', category_id: '' })
const creatingItem = ref(false)

// Filtri lato client sulla lista piatti: con tanti articoli scorrere una
// griglia piatta diventa ingestibile, meglio poter restringere subito per
// nome/categoria/disponibilita' (i piatti sono gia' tutti caricati in
// memoria, niente bisogno di richieste al server per filtrare).
const searchQuery = ref('')
const filterCategoryId = ref('')
const filterAvailability = ref('all')

const filteredItems = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return items.value.filter((item) => {
    if (query && !item.name.toLowerCase().includes(query)) return false
    if (filterCategoryId.value && item.category_id !== Number(filterCategoryId.value)) return false
    if (filterAvailability.value === 'available' && !item.available) return false
    if (filterAvailability.value === 'unavailable' && item.available) return false
    return true
  })
})

// Le 3 macro-sezioni verticali in cui il cliente vede il menu (vedi
// MenuView.vue): ogni categoria va assegnata a una di queste.
const GROUP_LABELS = { food: 'Cibo', drink: 'Bevande', dessert: 'Dolci' }

async function loadAll() {
  const [categoriesRes, itemsRes, allergensRes] = await Promise.all([
    api.get('/admin/menu-categories', opts),
    api.get('/admin/menu-items', opts),
    api.get('/admin/allergens', opts),
  ])
  categories.value = categoriesRes.data
  items.value = itemsRes.data
  allergens.value = allergensRes.data
}

async function withErrorHandling(action) {
  error.value = null
  try {
    await action()
  } catch (e) {
    error.value = e.body?.message ?? e.message
  }
}

async function addCategory() {
  await withErrorHandling(async () => {
    await api.post('/admin/menu-categories', {
      name: newCategoryName.value,
      sort_order: categories.value.length,
      group: newCategoryGroup.value,
    }, opts)
    newCategoryName.value = ''
    newCategoryGroup.value = 'food'
    await loadAll()
  })
}

async function updateCategory(id, payload) {
  await withErrorHandling(async () => {
    await api.put(`/admin/menu-categories/${id}`, payload, opts)
    await loadAll()
  })
}

async function deleteCategory(id) {
  await withErrorHandling(async () => {
    await api.delete(`/admin/menu-categories/${id}`, opts)
    await loadAll()
  })
}

async function addItem() {
  await withErrorHandling(async () => {
    creatingItem.value = true
    try {
      await api.post('/admin/menu-items', {
        name: newItem.value.name,
        price: Number(newItem.value.price),
        category_id: Number(newItem.value.category_id),
      }, opts)
      newItem.value = { name: '', price: '', category_id: '' }
      await loadAll()
    } finally {
      creatingItem.value = false
    }
  })
}

async function updateItem(id, payload) {
  await withErrorHandling(async () => {
    await api.put(`/admin/menu-items/${id}`, payload, opts)
    await loadAll()
  })
}

async function deleteItem(id) {
  await withErrorHandling(async () => {
    await api.delete(`/admin/menu-items/${id}`, opts)
    await loadAll()
  })
}

// L'upload foto (in MenuItemRow) aggiorna gia' il piatto lato server:
// qui basta sostituire la riga in memoria, senza un giro completo di reload.
function onImageUpdated(updatedItem) {
  const index = items.value.findIndex((item) => item.id === updatedItem.id)
  if (index !== -1) items.value[index] = updatedItem
}

onMounted(loadAll)
</script>

<template>
  <AdminLayout>
    <h1>Gestione menu</h1>
    <p v-if="error" class="error">{{ error }}</p>

    <section>
      <h2>Categorie</h2>
      <table>
        <thead><tr><th>Nome</th><th>Ordine</th><th>Sezione</th><th></th></tr></thead>
        <tbody>
          <CategoryRow
            v-for="category in categories"
            :key="category.id"
            :category="category"
            @update="updateCategory"
            @delete="deleteCategory"
          />
        </tbody>
      </table>
      <form class="inline-form" @submit.prevent="addCategory">
        <input v-model="newCategoryName" type="text" placeholder="Nuova categoria" required />
        <select v-model="newCategoryGroup" required>
          <option v-for="(label, value) in GROUP_LABELS" :key="value" :value="value">{{ label }}</option>
        </select>
        <button type="submit">Aggiungi categoria</button>
      </form>
    </section>

    <section>
      <h2>Piatti</h2>

      <form class="new-item-panel inline-form" @submit.prevent="addItem">
        <label>
          Nome piatto
          <input v-model="newItem.name" type="text" placeholder="Nome piatto" required />
        </label>
        <label>
          Prezzo
          <input v-model="newItem.price" type="number" step="0.01" min="0" placeholder="Prezzo" required />
        </label>
        <label>
          Categoria
          <select v-model="newItem.category_id" required>
            <option value="" disabled>Categoria</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </label>
        <button type="submit" :disabled="creatingItem">
          {{ creatingItem ? 'Aggiunta...' : 'Aggiungi piatto' }}
        </button>
      </form>
      <p class="hint">
        Dopo aver aggiunto il piatto, carica la foto passando il mouse sulla
        sua card qui sotto (o toccandola da telefono).
      </p>

      <div class="filters">
        <input
          v-model="searchQuery"
          type="search"
          placeholder="Cerca piatto per nome..."
          class="search-input"
        />
        <select v-model="filterCategoryId">
          <option value="">Tutte le categorie</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        <select v-model="filterAvailability">
          <option value="all">Disponibili e non</option>
          <option value="available">Solo disponibili</option>
          <option value="unavailable">Solo non disponibili</option>
        </select>
        <span class="results-count">{{ filteredItems.length }} di {{ items.length }} piatti</span>
      </div>

      <p v-if="items.length && !filteredItems.length" class="hint">
        Nessun piatto corrisponde ai filtri.
      </p>

      <ul class="items-grid">
        <MenuItemRow
          v-for="item in filteredItems"
          :key="item.id"
          :item="item"
          :categories="categories"
          :allergens="allergens"
          @update="updateItem"
          @delete="deleteItem"
          @image-updated="onImageUpdated"
        />
      </ul>
    </section>
  </AdminLayout>
</template>

<style scoped>
h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
}

h2 {
  color: #d85a30;
  font-size: 1.1rem;
  margin-top: 2rem;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  text-align: left;
  padding: 0.4rem 0.5rem;
  border-bottom: 1px solid #eee;
  font-size: 0.9rem;
}

.inline-form {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 0.75rem;
  margin-top: 0.75rem;
}

.inline-form label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.8rem;
  color: #412402;
  font-weight: 600;
}

.inline-form input,
.inline-form select {
  padding: 0.45rem 0.6rem;
  border: 1px solid #ccc;
  border-radius: 0.5rem;
  font: inherit;
}

.new-item-panel {
  background: white;
  border: 1px solid #f0f0f0;
  border-radius: 0.75rem;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(65, 36, 2, 0.06);
}

.hint {
  color: #666;
  font-size: 0.85rem;
  margin: 0.6rem 0 0;
}

.filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.6rem;
  margin-top: 1.25rem;
}

.search-input {
  flex: 1 1 14rem;
}

.filters input,
.filters select {
  padding: 0.45rem 0.6rem;
  border: 1px solid #ccc;
  border-radius: 0.5rem;
  font: inherit;
}

.results-count {
  color: #666;
  font-size: 0.8rem;
  white-space: nowrap;
  margin-left: auto;
}

.items-grid {
  list-style: none;
  padding: 0;
  margin: 1rem 0 0;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
  gap: 1rem;
}

button {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 0.9rem;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
}

button:disabled {
  opacity: 0.6;
  cursor: default;
}

.error {
  color: #d85a30;
}
</style>
