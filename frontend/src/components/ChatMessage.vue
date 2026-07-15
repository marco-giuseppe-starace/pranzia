<script setup>
defineProps({
  role: { type: String, required: true }, // 'user' | 'assistant'
  text: { type: String, required: true },
})
</script>

<template>
  <p class="message" :class="role">{{ text }}</p>
</template>

<style scoped>
.message {
  max-width: 80%;
  padding: 0.5rem 0.75rem;
  border-radius: 0.75rem;
  margin: 0.35rem 0;
  font-family: 'Inter', system-ui, sans-serif;
  white-space: pre-wrap;
}

.message.user {
  align-self: flex-end;
  background: #ef9f27;
  color: #412402;
}

.message.assistant {
  align-self: flex-start;
  background: #f4f1ea;
  color: #1a1a1a;
}
</style>
