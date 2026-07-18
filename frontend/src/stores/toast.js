import { defineStore } from 'pinia'

let hideTimer = null

export const useToastStore = defineStore('toast', {
  state: () => ({
    message: '',
    icon: '✅',
    visible: false,
  }),

  actions: {
    show(message, icon = '✅') {
      clearTimeout(hideTimer)
      this.message = message
      this.icon = icon
      this.visible = true
      hideTimer = setTimeout(() => {
        this.visible = false
      }, 3000)
    },
  },
})
