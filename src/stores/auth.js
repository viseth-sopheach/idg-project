import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false
  }),
  actions: {
    async login(credentials) {
      await api.get('/sanctum/csrf-cookie')
      const response = await api.post('/login', credentials)
      await this.fetchUser()
      return response
    },
    async fetchUser() {
      try {
        const response = await api.get('/me')
        this.user = response.data
        this.isAuthenticated = true
      } catch (error) {
        this.user = null
        this.isAuthenticated = false
      }
    },
    async logout() {
      await api.post('/logout')
      this.user = null
      this.isAuthenticated = false
    }
  }
})