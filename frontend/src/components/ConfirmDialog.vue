<script setup>
import { useConfirmDialogStore } from '../stores/confirmDialog.js'
import { useI18n } from '../i18n/index.js'

const store = useConfirmDialogStore()
const { t } = useI18n()
</script>

<template>
  <Teleport to="body">
    <div v-if="store.isOpen" class="confirm-overlay" @click.self="store.respond(false)">
      <div class="confirm-modal">
        <p class="message">{{ store.message }}</p>
        <div class="actions">
          <button type="button" class="secondary" @click="store.respond(false)">
            {{ t('confirmDialog.cancel') }}
          </button>
          <button type="button" :class="store.danger ? 'danger' : 'primary'" @click="store.respond(true)">
            {{ t('confirmDialog.confirm') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(65, 36, 2, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 200;
}

.confirm-modal {
  background: white;
  border-radius: 0.75rem;
  padding: 1.25rem;
  width: 100%;
  max-width: 24rem;
  font-family: 'Inter', system-ui, sans-serif;
}

.message {
  margin: 0 0 1rem;
  color: #1a1a1a;
  line-height: 1.4;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

button {
  border: none;
  border-radius: 0.4rem;
  padding: 0.5rem 0.9rem;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
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
