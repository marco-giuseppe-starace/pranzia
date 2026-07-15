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
  padding: 0.6rem 0.9rem;
  border-radius: 1rem;
  margin: 0.35rem 0;
  font-family: 'Inter', system-ui, sans-serif;
  line-height: 1.5;
  white-space: pre-wrap;
  box-shadow: 0 1px 2px rgba(65, 36, 2, 0.08);
}

.message.user {
  align-self: flex-end;
  background: #ef9f27;
  color: #412402;
  border-bottom-right-radius: 0.25rem;
}

.message.assistant {
  align-self: flex-start;
  background: #f4f1ea;
  color: #1a1a1a;
  border-bottom-left-radius: 0.25rem;
}
</style>
