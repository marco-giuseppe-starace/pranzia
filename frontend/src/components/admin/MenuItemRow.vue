<script setup>
import { ref } from 'vue'

const props = defineProps({
  item: { type: Object, required: true },
  categories: { type: Array, required: true },
  allergens: { type: Array, required: true },
})

const emit = defineEmits(['update', 'delete'])

const editing = ref(false)
const name = ref(props.item.name)
const price = ref(props.item.price)
const categoryId = ref(props.item.category_id)
const available = ref(props.item.available)
const allergenIds = ref(props.item.allergens.map((a) => a.id))

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
</script>

<template>
  <tr v-if="!editing">
    <td>{{ item.name }}</td>
    <td>{{ categoryName(item.category_id) }}</td>
    <td>{{ Number(item.price).toFixed(2) }} &euro;</td>
    <td>{{ item.available ? 'Sì' : 'No' }}</td>
    <td>{{ item.allergens.map((a) => a.name).join(', ') || '—' }}</td>
    <td>
      <button type="button" @click="editing = true">Modifica</button>
      <button type="button" @click="emit('delete', item.id)">Elimina</button>
    </td>
  </tr>
  <tr v-else class="editing">
    <td colspan="6">
      <div class="edit-form">
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

        <div>
          <button type="button" @click="save">Salva</button>
          <button type="button" @click="editing = false">Annulla</button>
        </div>
      </div>
    </td>
  </tr>
</template>

<style scoped>
.edit-form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 0.5rem 0;
}

.edit-form label {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  font-size: 0.85rem;
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
</style>
