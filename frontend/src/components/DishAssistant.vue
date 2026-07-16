<script setup>
import { ref } from 'vue'
import { api } from '../api/client.js'
import { useCartStore } from '../stores/cart.js'
import { useSessionStore } from '../stores/session.js'
import { useI18n } from '../i18n/index.js'
import ChatMessage from './ChatMessage.vue'

const props = defineProps({
  item: { type: Object, required: true },
})

const emit = defineEmits(['close'])

const cart = useCartStore()
const session = useSessionStore()
const { t } = useI18n()

const messages = ref([])
const question = ref('')
const sending = ref(false)
const notes = ref('')
const justAdded = ref(false)

async function send() {
  const text = question.value.trim()
  if (!text || sending.value) return

  messages.value.push({ role: 'user', text })
  question.value = ''
  sending.value = true

  try {
    // Il piatto viene indicato nel testo della domanda (non serve un
    // parametro/endpoint dedicato): il menu completo e' gia' nel contesto
    // IA, questo serve solo a far capire a quale piatto si riferisce.
    const response = await api.post('/ai/ask', {
      session_id: session.sessionId,
      question: `Riguardo al piatto "${props.item.name}": ${text}`,
      language: session.language,
    })
    messages.value.push({ role: 'assistant', text: response.text })
  } catch (e) {
    messages.value.push({ role: 'assistant', text: e.message })
  } finally {
    sending.value = false
  }
}

function addToCart() {
  cart.add(props.item, notes.value.trim())
  justAdded.value = true
  setTimeout(() => {
    justAdded.value = false
  }, 1400)
}
</script>

<template>
  <div class="backdrop" @click.self="emit('close')">
    <div class="modal" role="dialog" :aria-label="item.name">
      <header>
        <h2>{{ item.name }}</h2>
        <button type="button" class="close" :aria-label="t('dishAssistant.close')" @click="emit('close')">&times;</button>
      </header>

      <p class="intro">{{ t('dishAssistant.intro') }}</p>

      <div class="messages">
        <ChatMessage v-for="(message, index) in messages" :key="index" v-bind="message" />
        <div v-if="sending" class="typing" aria-live="polite">
          <span></span><span></span><span></span>
        </div>
      </div>

      <form class="composer" @submit.prevent="send">
        <input v-model="question" type="text" :placeholder="t('dishAssistant.placeholder')" :disabled="sending" />
        <button type="submit" :disabled="sending">{{ t('dishAssistant.send') }}</button>
      </form>

      <div class="cart-action">
        <label class="notes-label">
          {{ t('dishAssistant.notesLabel') }}
          <input v-model="notes" type="text" :placeholder="t('dishAssistant.notesPlaceholder')" />
        </label>
        <button type="button" class="add" @click="addToCart">{{ t('dishAssistant.addToCart') }}</button>
        <span v-if="justAdded" class="added-hint">{{ t('dishAssistant.added') }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.backdrop {
  position: fixed;
  inset: 0;
  background: rgba(65, 36, 2, 0.4);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 100;
}

@media (min-width: 640px) {
  .backdrop {
    align-items: center;
  }
}

.modal {
  background: white;
  width: 100%;
  max-width: 480px;
  max-height: 85vh;
  border-radius: 1rem 1rem 0 0;
  padding: 1rem 1.25rem 1.25rem;
  display: flex;
  flex-direction: column;
  font-family: 'Inter', system-ui, sans-serif;
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.15);
}

@media (min-width: 640px) {
  .modal {
    border-radius: 1rem;
    max-height: 80vh;
  }
}

header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.25rem;
}

h2 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
  font-size: 1.2rem;
}

.close {
  background: none;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  color: #777;
  cursor: pointer;
  padding: 0.25rem;
}

.intro {
  font-size: 0.85rem;
  color: #666;
  margin: 0 0 0.75rem;
}

.messages {
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  flex: 1;
  min-height: 3rem;
}

.messages:empty {
  display: none;
}

.typing {
  align-self: flex-start;
  display: flex;
  gap: 0.3rem;
  padding: 0.6rem 0.9rem;
  margin: 0.35rem 0;
  background: #f4f1ea;
  border-radius: 0.75rem;
}

.typing span {
  width: 0.4rem;
  height: 0.4rem;
  border-radius: 50%;
  background: #9c8f78;
  animation: typing-bounce 1.2s infinite ease-in-out;
}

.typing span:nth-child(2) {
  animation-delay: 0.15s;
}

.typing span:nth-child(3) {
  animation-delay: 0.3s;
}

@keyframes typing-bounce {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.5;
  }
  30% {
    transform: translateY(-0.25rem);
    opacity: 1;
  }
}

.composer {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.composer input {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 0.5rem;
}

.composer button {
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  font-weight: 600;
  cursor: pointer;
}

.cart-action {
  margin-top: 1rem;
  padding-top: 0.75rem;
  border-top: 1px solid #f0f0f0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.notes-label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.8rem;
  color: #555;
}

.notes-label input {
  padding: 0.45rem;
  border: 1px solid #ccc;
  border-radius: 0.5rem;
  font-size: 0.9rem;
}

.add {
  align-self: flex-start;
  background: #ef9f27;
  color: #412402;
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.add:hover {
  background: #e08f16;
}

.add:active {
  transform: scale(0.96);
}

.added-hint {
  font-size: 0.75rem;
  color: #2e7d32;
  font-weight: 600;
  animation: fade-in-out 1.4s ease;
}

@keyframes fade-in-out {
  0% {
    opacity: 0;
    transform: translateY(-0.15rem);
  }
  15%,
  70% {
    opacity: 1;
    transform: translateY(0);
  }
  100% {
    opacity: 0;
  }
}
</style>
