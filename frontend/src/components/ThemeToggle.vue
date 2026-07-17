<script setup>
import { useThemeStore } from '../stores/theme'
import { strings } from '../strings/pt-BR'

const themeStore = useThemeStore()
</script>

<template>
  <button
    type="button"
    class="theme-toggle"
    :aria-label="themeStore.theme === 'dark' ? strings.themeToggle.enableLight : strings.themeToggle.enableDark"
    :title="themeStore.theme === 'dark' ? strings.themeToggle.enableLight : strings.themeToggle.enableDark"
    @click="themeStore.toggle()"
  >
    {{ themeStore.theme === 'dark' ? '☀️' : '🌙' }}
  </button>
</template>

<style scoped>
.theme-toggle {
  position: fixed;
  right: 16px;
  bottom: 16px;
  z-index: 1000;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 1px solid var(--border);
  background: var(--panel-bg);
  color: var(--text-h);
  font-size: 17px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: var(--shadow);
  transition:
    border-color 0.15s ease,
    transform 0.15s ease;
}

.theme-toggle:hover {
  border-color: var(--accent-border);
}

.theme-toggle:active {
  transform: translateY(1px);
}
</style>
