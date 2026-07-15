import { defineStore } from 'pinia'

const STORAGE_KEY = 'pranzia.session'

function loadPersisted() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY)) ?? {}
  } catch {
    return {}
  }
}

export const useSessionStore = defineStore('session', {
  state: () => ({
    sessionId: null,
    tableId: null,
    language: 'it',
    ...loadPersisted(),
  }),
  actions: {
    setSession({ id, table_id: tableId }) {
      this.sessionId = id
      this.tableId = tableId
      this.persist()
    },
    setLanguage(language) {
      this.language = language
      this.persist()
    },
    persist() {
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({ sessionId: this.sessionId, tableId: this.tableId, language: this.language })
      )
    },
  },
})
