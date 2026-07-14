import { defineStore } from 'pinia'
import http from '../services/http'

const TOKEN_STORAGE_KEY = 'auth_token'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem(TOKEN_STORAGE_KEY) || null,
    user: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    setToken(token) {
      this.token = token

      if (token) {
        localStorage.setItem(TOKEN_STORAGE_KEY, token)
      } else {
        localStorage.removeItem(TOKEN_STORAGE_KEY)
      }
    },

    async register({ name, email, password, password_confirmation }) {
      const { data } = await http.post('/v1/auth/register', {
        name,
        email,
        password,
        password_confirmation,
      })

      return data.data
    },

    async login({ email, password }) {
      const { data } = await http.post('/v1/auth/login', { email, password })

      this.setToken(data.data.token)
      this.user = data.data.user

      return this.user
    },

    async fetchMe() {
      const { data } = await http.get('/v1/auth/me')

      this.user = data.data

      return this.user
    },

    async logout() {
      try {
        await http.post('/v1/auth/logout')
      } finally {
        this.setToken(null)
        this.user = null
      }
    },
  },
})
