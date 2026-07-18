<script setup>
import { useToastStore } from '../stores/toast'

const toastStore = useToastStore()
</script>

<template>
  <div class="toast-box" :class="{ 'toast-box--show': toastStore.visible }" role="status" aria-live="polite">
    <span class="toast-box__icon">{{ toastStore.icon }}</span>
    <span>{{ toastStore.message }}</span>
  </div>
</template>

<style scoped>
.toast-box {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background: var(--panel-bg);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-h);
  box-shadow: var(--shadow);
  transform: translateY(80px);
  opacity: 0;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  z-index: 999;
  pointer-events: none;
  max-width: min(300px, calc(100vw - 48px));
}
.toast-box--show {
  transform: translateY(0);
  opacity: 1;
}
.toast-box__icon {
  font-size: 18px;
}
</style>
