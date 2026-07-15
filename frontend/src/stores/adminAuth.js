import { defineStore } from 'pinia'
import { api } from '../api/client.js'

const STORAGE_KEY = 'pranzia.admin.token'

export const useAdminAuthStore = defineStore('adminAuth', {
  state: () => ({
    token: localStorage.getItem(STORAGE_KEY),
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },
  actions: {
    async login(email, password) {
      const response = await api.post('/admin/login', { email, password })
      this.token = response.token
      localStorage.setItem(STORAGE_KEY, this.token)
    },
    async logout() {
      try {
        await api.post('/admin/logout', undefined, { token: this.token })
      } finally {
        this.token = null
        localStorage.removeItem(STORAGE_KEY)
      }
    },
  },
})
