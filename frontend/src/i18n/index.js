import { computed } from 'vue'
import { useSessionStore } from '../stores/session.js'
import it from './it.js'
import en from './en.js'

const dictionaries = { it, en }

// Dizionario statico minimale per it/en: una libreria come vue-i18n
// sarebbe sovradimensionata per le poche stringhe di interfaccia di questa
// PWA. La traduzione dei contenuti del menu/chat resta compito dell'IA.
export function useI18n() {
  const session = useSessionStore()

  const t = (key) => {
    const dictionary = dictionaries[session.language] ?? dictionaries.it
    return key.split('.').reduce((value, segment) => value?.[segment], dictionary) ?? key
  }

  const language = computed(() => session.language)

  return { t, language }
}

export const availableLanguages = Object.keys(dictionaries)
