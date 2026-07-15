<script setup>
import { ref } from 'vue'
import { api } from '../api/client.js'
import { useSessionStore } from '../stores/session.js'
import { useI18n } from '../i18n/index.js'
import ChatMessage from '../components/ChatMessage.vue'

const session = useSessionStore()
const { t } = useI18n()

const messages = ref([])
const question = ref('')
const sending = ref(false)

async function send() {
  const text = question.value.trim()
  if (!text || sending.value) return

  messages.value.push({ role: 'user', text })
  question.value = ''
  sending.value = true

  try {
    const response = await api.post('/ai/ask', {
      session_id: session.sessionId,
      question: text,
      language: session.language,
    })
    messages.value.push({ role: 'assistant', text: response.text })
  } catch (e) {
    messages.value.push({ role: 'assistant', text: e.message })
  } finally {
    sending.value = false
  }
}

async function recommend() {
  if (sending.value) return
  sending.value = true

  try {
    const response = await api.post('/ai/recommend', { session_id: session.sessionId })
    messages.value.push({ role: 'assistant', text: response.text })
  } catch (e) {
    messages.value.push({ role: 'assistant', text: e.message })
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <main class="chat">
    <h1>{{ t('chat.title') }}</h1>

    <button type="button" class="recommend" @click="recommend" :disabled="sending">
      {{ t('chat.recommend') }}
    </button>

    <div class="messages">
      <ChatMessage v-for="(message, index) in messages" :key="index" v-bind="message" />
    </div>

    <form class="composer" @submit.prevent="send">
      <input v-model="question" type="text" :placeholder="t('chat.placeholder')" :disabled="sending" />
      <button type="submit" :disabled="sending">{{ t('chat.send') }}</button>
    </form>
  </main>
</template>

<style scoped>
.chat {
  max-width: 640px;
  margin: 0 auto;
  padding: 1rem 1.5rem 3rem;
  display: flex;
  flex-direction: column;
  font-family: 'Inter', system-ui, sans-serif;
}

h1 {
  font-family: 'Baloo 2', sans-serif;
  color: #412402;
}

.recommend {
  align-self: flex-start;
  background: #d85a30;
  color: white;
  border: none;
  border-radius: 999px;
  padding: 0.4rem 1rem;
  margin-bottom: 1rem;
  cursor: pointer;
}

.messages {
  display: flex;
  flex-direction: column;
  min-height: 12rem;
}

.composer {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
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
</style>
