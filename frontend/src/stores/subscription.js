import { defineStore } from 'pinia'
import http from '../services/http'

export const useSubscriptionStore = defineStore('subscription', {
  state: () => ({
    subscription: null,
  }),

  actions: {
    async fetchSubscription() {
      const { data } = await http.get('/v1/subscription/me')
      this.subscription = data.data

      return this.subscription
    },
  },
})
