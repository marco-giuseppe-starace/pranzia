<script setup>
import { ref } from 'vue'
import { api } from '../../api/client.js'
import { useAdminAuthStore } from '../../stores/adminAuth.js'

const props = defineProps({
  item: { type: Object, required: true },
  categories: { type: Array, required: true },
  allergens: { type: Array, required: true },
})

const emit = defineEmits(['update', 'delete', 'image-updated'])

const adminAuth = useAdminAuthStore()

const editing = ref(false)
const name = ref(props.item.name)
const price = ref(props.item.price)
const categoryId = ref(props.item.category_id)
const available = ref(props.item.available)
const allergenIds = ref(props.item.allergens.map((a) => a.id))

const uploading = ref(false)
const uploadError = ref(null)

function categoryName(id) {
  return props.categories.find((c) => c.id === id)?.name ?? '—'
}

function toggleAllergen(id) {
  allergenIds.value = allergenIds.value.includes(id)
    ? allergenIds.value.filter((a) => a !== id)
    : [...allergenIds.value, id]
}

function save() {
  emit('update', props.item.id, {
    name: name.value,
    price: Number(price.value),
    category_id: Number(categoryId.value),
    available: available.value,
    allergen_ids: allergenIds.value,
  })
  editing.value = false
}

function remove() {
  if (!window.confirm(`Eliminare il piatto "${props.item.name}"?`)) return
  emit('delete', props.item.id)
}

// L'upload parte subito alla scelta del file (niente pulsante di conferma
// separato): piu' rapido per lo staff che deve caricare tante foto di fila.
async function onFileSelected(event) {
  const file = event.target.files[0]
  event.target.value = ''
  if (!file) return

  uploadError.value = null
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('image', file)
    const response = await api.post(`/admin/menu-items/${props.item.id}/image`, formData, {
      token: adminAuth.token,
    })
    emit('image-updated', response.data)
  } catch (e) {
    uploadError.value = e.body?.message ?? 'Caricamento immagine non riuscito.'
  } finally {
    uploading.value = false
  }
}
</script>

<template>
  <li class="item-card" :class="{ unavailable: !item.available }">
    <label class="thumb">
      <img v-if="item.image_url" :src="item.image_url" :alt="item.name" />
      <span v-else class="thumb-placeholder" aria-hidden="true">{{ item.name.charAt(0) }}</span>
      <span class="thumb-overlay">{{ uploading ? 'Caricamento...' : 'Cambia foto' }}</span>
      <input type="file" accept="image/*" :disabled="uploading" @change="onFileSelected" />
    </label>

    <div class="body">
      <p v-if="uploadError" class="error">{{ uploadError }}</p>

      <template v-if="!editing">
        <div class="top-row">
          <h3>{{ item.name }}</h3>
          <span class="price">{{ Number(item.price).toFixed(2) }} &euro;</span>
        </div>
        <div class="meta">
          <span class="badge category">{{ categoryName(item.category_id) }}</span>
          <span class="badge" :class="item.available ? 'available' : 'unavailable-badge'">
            {{ item.available ? 'Disponibile' : 'Non disponibile' }}
          </span>
        </div>
        <ul v-if="item.allergens.length" class="allergens">
          <li v-for="allergen in item.allergens" :key="allergen.id">{{ allergen.name }}</li>
        </ul>

        <div class="actions">
          <button type="button" class="primary" @click="editing = true">Modifica</button>
          <button type="button" class="danger" @click="remove">Elimina</button>
        </div>
      </template>

      <div v-else class="edit-form">
        <label>Nome <input v-model="name" type="text" /></label>
        <label>
          Categoria
          <select v-model="categoryId">
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </label>
        <label>Prezzo <input v-model="price" type="number" step="0.01" min="0" /></label>
        <label class="checkbox"><input v-model="available" type="checkbox" /> Disponibile</label>

        <fieldset>
          <legend>Allergeni</legend>
          <label v-for="allergen in allergens" :key="allergen.id" class="checkbox">
            <input
              type="checkbox"
              :checked="allergenIds.includes(allergen.id)"
              @change="toggleAllergen(allergen.id)"
            />
            {{ allergen.name }}
          </label>
        </fieldset>

        <div class="actions">
          <button type="button" class="primary" @click="save">Salva</button>
          <button type="button" class="secondary" @click="editing = false">Annulla</button>
        </div>
      </div>
    </div>
  </li>
</template>

<style scoped>
.item-card {
  display: flex;
  flex-direction: column;
  background: white;
  border: 1px solid #f0f0f0;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(65, 36, 2, 0.06);
  font-family: 'Inter', system-ui, sans-serif;
}

.item-card.unavailable {
  opacity: 0.7;
}

.thumb {
  position: relative;
  display: block;
  width: 100%;
  height: 8rem;
  flex-shrink: 0;
  background: linear-gradient(135deg, #fdf1de, #f6d9a8);
  cursor: pointer;
}

.thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.thumb-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Baloo 2', sans-serif;
  font-size: 2.5rem;
  font-weight: 700;
  color: #d85a30;
}

.thumb-overlay {
  position: absolute;
  inset: auto 0 0 0;
  background: rgba(65, 36, 2, 0.72);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  text-align: center;
  padding: 0.3rem;
  opacity: 0;
  transition: opacity 0.15s ease;
}

.thumb:hover .thumb-overlay,
.thumb:focus-within .thumb-overlay {
  opacity: 1;
}

.thumb input[type='file'] {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
}

.body {
  padding: 0.75rem 0.9rem 0.9rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.top-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.5rem;
}

h3 {
  margin: 0;
  color: #1a1a1a;
  font-size: 1rem;
}

.price {
  font-weight: 700;
  color: #412402;
  white-space: nowrap;
}

.meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
}

.badge {
  font-size: 0.72rem;
  font-weight: 600;
  border-radius: 999px;
  padding: 0.15rem 0.6rem;
}

.badge.category {
  background: #f4f1ea;
  color: #412402;
}

.badge.available {
  background: #e6f2e1;
  color: #3f6b31;
}

.badge.unavailable-badge {
  background: #fbe4dc;
  color: #a13f1e;
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

.actions {
  display: flex;
  gap: 0.4rem;
  margin-top: 0.25rem;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.edit-form label {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  font-size: 0.85rem;
}

.edit-form input[type='text'],
.edit-form input[type='number'],
.edit-form select {
  padding: 0.4rem;
  border: 1px solid #ccc;
  border-radius: 0.4rem;
  font: inherit;
}

.edit-form label.checkbox {
  flex-direction: row;
  align-items: center;
  gap: 0.4rem;
}

fieldset {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 1rem;
  border: 1px solid #eee;
  border-radius: 0.5rem;
}

legend {
  font-size: 0.8rem;
  color: #666;
  padding: 0 0.3rem;
}

button {
  border: none;
  border-radius: 0.4rem;
  padding: 0.4rem 0.75rem;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

button.primary {
  background: #ef9f27;
  color: #412402;
}

button.secondary {
  background: #f4f1ea;
  color: #412402;
}

button.danger {
  background: #d85a30;
  color: #fff;
}

.error {
  color: #d85a30;
  font-size: 0.8rem;
  margin: 0;
}
</style>
