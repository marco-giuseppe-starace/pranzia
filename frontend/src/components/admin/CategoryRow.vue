<script setup>
import { ref } from 'vue'

const props = defineProps({
  category: { type: Object, required: true },
})

const emit = defineEmits(['update', 'delete'])

const editing = ref(false)
const name = ref(props.category.name)
const sortOrder = ref(props.category.sort_order)

function save() {
  emit('update', props.category.id, { name: name.value, sort_order: Number(sortOrder.value) })
  editing.value = false
}
</script>

<template>
  <tr>
    <template v-if="editing">
      <td><input v-model="name" type="text" /></td>
      <td><input v-model="sortOrder" type="number" min="0" style="width: 4rem" /></td>
      <td>
        <button type="button" class="primary" @click="save">Salva</button>
        <button type="button" class="secondary" @click="editing = false">Annulla</button>
      </td>
    </template>
    <template v-else>
      <td>{{ category.name }}</td>
      <td>{{ category.sort_order }}</td>
      <td>
        <button type="button" class="primary" @click="editing = true">Modifica</button>
        <button type="button" class="danger" @click="emit('delete', category.id)">Elimina</button>
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
