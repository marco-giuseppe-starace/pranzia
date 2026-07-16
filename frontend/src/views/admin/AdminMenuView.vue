<script setup>
import { ref, onMounted } from 'vue'
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
    await api.post('/admin/menu-items', {
      name: newItem.value.name,
      price: Number(newItem.value.price),
      category_id: Number(newItem.value.category_id),
    }, opts)
    newItem.value = { name: '', price: '', category_id: '' }
    await loadAll()
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
      <table>
        <thead>
          <tr><th>Nome</th><th>Categoria</th><th>Prezzo</th><th>Disponibile</th><th>Allergeni</th><th></th></tr>
        </thead>
        <tbody>
          <MenuItemRow
            v-for="item in items"
            :key="item.id"
            :item="item"
            :categories="categories"
            :allergens="allergens"
            @update="updateItem"
            @delete="deleteItem"
          />
        </tbody>
      </table>
      <form class="inline-form" @submit.prevent="addItem">
        <input v-model="newItem.name" type="text" placeholder="Nome piatto" required />
        <input v-model="newItem.price" type="number" step="0.01" min="0" placeholder="Prezzo" required />
        <select v-model="newItem.category_id" required>
          <option value="" disabled>Categoria</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        <button type="submit">Aggiungi piatto</button>
      </form>
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
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.inline-form input,
.inline-form select {
  padding: 0.35rem;
  border: 1px solid #ccc;
  border-radius: 0.4rem;
}

button {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.4rem;
  padding: 0.3rem 0.6rem;
  cursor: pointer;
  font-size: 0.85rem;
}

.error {
  color: #d85a30;
}
</style>
