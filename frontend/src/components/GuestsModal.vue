<script setup>
import { ref, watch } from 'vue'
import { useSessionStore } from '../stores/session.js'
import { useI18n } from '../i18n/index.js'

const props = defineProps({
  // Solo quando si corregge un valore gia' inserito si puo' chiudere
  // senza confermare: la primissima richiesta (guests ancora null) resta
  // bloccante di proposito.
  dismissable: { type: Boolean, default: false },
})

const session = useSessionStore()
const { t } = useI18n()

const MIN = 1
const MAX = 50

const value = ref(session.guests ?? '')
const saving = ref(false)
const error = ref(null)

// Se il modal si riapre (es. "modifica coperti" dopo averlo gia' chiuso),
// riparte dal valore attuale invece che dall'ultima bozza lasciata a meta'.
watch(
  () => session.guestsModalForceOpen,
  (open) => {
    if (open) value.value = session.guests ?? ''
  }
)

const isValid = () => {
  const n = Number(value.value)
  return Number.isInteger(n) && n >= MIN && n <= MAX
}

async function confirm() {
  if (!isValid()) return
  error.value = null
  saving.value = true
  try {
    await session.updateGuests(Number(value.value))
    session.guestsModalForceOpen = false
  } catch (e) {
    error.value = e.body?.message ?? e.message
  } finally {
    saving.value = false
  }
}

function cancel() {
  if (!props.dismissable) return
  session.guestsModalForceOpen = false
}
</script>

<template>
  <div class="guests-overlay" @click.self="cancel">
    <div class="guests-modal">
      <h2>{{ t('guests.title') }}</h2>
      <p class="hint">{{ t('guests.hint') }}</p>

      <input
        v-model="value"
        type="number"
        :min="MIN"
        :max="MAX"
        inputmode="numeric"
        autofocus
        @keyup.enter="confirm"
      />

      <p v-if="error" class="error">{{ error }}</p>

      <div class="actions">
        <button v-if="dismissable" type="button" class="secondary" @click="cancel">
          {{ t('confirmDialog.cancel') }}
        </button>
        <button type="button" class="primary" :disabled="!isValid() || saving" @click="confirm">
          {{ saving ? '...' : t('guests.confirm') }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.guests-overlay {
  position: fixed;
  inset: 0;
  background: rgba(65, 36, 2, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 300;
}

.guests-modal {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  width: 100%;
  max-width: 22rem;
  font-family: 'Inter', system-ui, sans-serif;
  text-align: center;
}

h2 {
  margin: 0 0 0.4rem;
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
  font-size: 1.3rem;
}

.hint {
  margin: 0 0 1rem;
  color: #666;
  font-size: 0.88rem;
}

input {
  width: 100%;
  box-sizing: border-box;
  padding: 0.7rem;
  border: 1px solid #ccc;
  border-radius: 0.5rem;
  font-size: 1.4rem;
  text-align: center;
  font-weight: 700;
  color: #412402;
}

.error {
  color: #d85a30;
  font-size: 0.85rem;
  font-weight: 600;
  margin: 0.6rem 0 0;
}

.actions {
  margin-top: 1.1rem;
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}

button {
  border: none;
  border-radius: 0.5rem;
  padding: 0.55rem 1.3rem;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.95rem;
}

button.primary {
  background: #ef9f27;
  color: #412402;
}

button.primary:disabled {
  opacity: 0.6;
  cursor: default;
}

button.secondary {
  background: #f4f1ea;
  color: #412402;
}
</style>
