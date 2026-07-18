import { defineStore } from 'pinia'

const STORAGE_KEY = 'sidebar_collapsed'

export const useSidebarStore = defineStore('sidebar', {
  state: () => ({
    collapsed: localStorage.getItem(STORAGE_KEY) === 'true',
  }),

  actions: {
    toggle() {
      this.collapsed = !this.collapsed
      localStorage.setItem(STORAGE_KEY, String(this.collapsed))
    },
  },
})
