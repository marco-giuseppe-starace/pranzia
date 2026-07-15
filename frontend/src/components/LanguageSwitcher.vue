<script setup>
import { useSessionStore } from '../stores/session.js'
import { useI18n, availableLanguages } from '../i18n/index.js'

const session = useSessionStore()
const { t } = useI18n()

const labels = { it: 'Italiano', en: 'English' }
</script>

<template>
  <label class="language-switcher">
    <span class="sr-only">{{ t('language.label') }}</span>
    <select :value="session.language" @change="session.setLanguage($event.target.value)">
      <option v-for="code in availableLanguages" :key="code" :value="code">
        {{ labels[code] ?? code }}
      </option>
    </select>
  </label>
</template>

<style scoped>
.language-switcher select {
  border: 1px solid #412402;
  border-radius: 0.5rem;
  padding: 0.25rem 0.5rem;
  font-family: inherit;
  background: white;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
}
</style>
