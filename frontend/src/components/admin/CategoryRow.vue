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
        <button type="button" @click="save">Salva</button>
        <button type="button" @click="editing = false">Annulla</button>
      </td>
    </template>
    <template v-else>
      <td>{{ category.name }}</td>
      <td>{{ category.sort_order }}</td>
      <td>
        <button type="button" @click="editing = true">Modifica</button>
        <button type="button" @click="emit('delete', category.id)">Elimina</button>
      </td>
    </template>
  </tr>
</template>
