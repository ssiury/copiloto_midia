import { defineStore } from 'pinia'

const STORAGE_KEY = 'theme'
const media = window.matchMedia('(prefers-color-scheme: dark)')

function hasExplicitPreference() {
  return !!localStorage.getItem(STORAGE_KEY)
}

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme)
  // Bootstrap 5.3+ reads this attribute to switch its own components to dark mode.
  document.documentElement.setAttribute('data-bs-theme', theme)
}

export const useThemeStore = defineStore('theme', {
  state: () => ({
    theme: localStorage.getItem(STORAGE_KEY) || (media.matches ? 'dark' : 'light'),
  }),

  actions: {
    init() {
      applyTheme(this.theme)

      // Follow the OS preference live, but only until the user picks a theme explicitly.
      media.addEventListener('change', (event) => {
        if (hasExplicitPreference()) return
        this.theme = event.matches ? 'dark' : 'light'
        applyTheme(this.theme)
      })
    },

    set(theme) {
      this.theme = theme
      localStorage.setItem(STORAGE_KEY, theme)
      applyTheme(theme)
    },

    toggle() {
      this.set(this.theme === 'dark' ? 'light' : 'dark')
    },
  },
})
