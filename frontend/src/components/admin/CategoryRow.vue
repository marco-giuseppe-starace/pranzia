<script setup>
import { ref } from 'vue'
import { useConfirmDialogStore } from '../../stores/confirmDialog.js'

const props = defineProps({
  category: { type: Object, required: true },
})

const emit = defineEmits(['update', 'delete'])

const confirmDialog = useConfirmDialogStore()

const GROUP_LABELS = { food: 'Cibo', drink: 'Bevande', dessert: 'Dolci' }

const editing = ref(false)
const name = ref(props.category.name)
const sortOrder = ref(props.category.sort_order)
const group = ref(props.category.group)

function save() {
  emit('update', props.category.id, { name: name.value, sort_order: Number(sortOrder.value), group: group.value })
  editing.value = false
}

async function remove() {
  const confirmed = await confirmDialog.confirm(`Eliminare la categoria "${props.category.name}"?`, { danger: true })
  if (!confirmed) return
  emit('delete', props.category.id)
}
</script>

<template>
  <tr>
    <template v-if="editing">
      <td><input v-model="name" type="text" /></td>
      <td><input v-model="sortOrder" type="number" min="0" style="width: 4rem" /></td>
      <td>
        <select v-model="group">
          <option v-for="(label, value) in GROUP_LABELS" :key="value" :value="value">{{ label }}</option>
        </select>
      </td>
      <td>
        <button type="button" class="primary" @click="save">Salva</button>
        <button type="button" class="secondary" @click="editing = false">Annulla</button>
      </td>
    </template>
    <template v-else>
      <td>{{ category.name }}</td>
      <td>{{ category.sort_order }}</td>
      <td>{{ GROUP_LABELS[category.group] ?? category.group }}</td>
      <td>
        <button type="button" class="primary" @click="editing = true">Modifica</button>
        <button type="button" class="danger" @click="remove">Elimina</button>
      </td>
    </template>
  </tr>
</template>

<style scoped>
button {
  border: none;
  border-radius: 0.4rem;
  padding: 0.3rem 0.6rem;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  margin-right: 0.4rem;
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
</style>
