<script setup>
import { useI18n } from '../i18n/index.js'

const props = defineProps({
  allergens: { type: Array, required: true },
  modelValue: { type: Array, required: true },
})

const emit = defineEmits(['update:modelValue'])
const { t } = useI18n()

function toggle(id) {
  const next = props.modelValue.includes(id)
    ? props.modelValue.filter((excludedId) => excludedId !== id)
    : [...props.modelValue, id]

  emit('update:modelValue', next)
}
</script>

<template>
  <details class="allergen-filter" v-if="allergens.length">
    <summary>{{ t('menu.filterAllergens') }}</summary>
    <p class="hint">{{ t('menu.filterAllergensHint') }}</p>
    <label v-for="allergen in allergens" :key="allergen.id">
      <input
        type="checkbox"
        :checked="modelValue.includes(allergen.id)"
        @change="toggle(allergen.id)"
      />
      {{ allergen.name }}
    </label>
  </details>
</template>

<style scoped>
.allergen-filter {
  font-family: 'Inter', system-ui, sans-serif;
  margin-bottom: 1rem;
}

.allergen-filter label {
  display: block;
  padding: 0.25rem 0;
}

.hint {
  margin: 0.4rem 0 0.5rem;
  font-size: 0.8rem;
  color: #777;
}
</style>
