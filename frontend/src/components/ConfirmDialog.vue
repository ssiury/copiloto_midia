<script setup>
defineProps({
  open: { type: Boolean, default: false },
  icon: { type: String, default: '⚠️' },
  title: { type: String, required: true },
  text: { type: String, required: true },
  confirmLabel: { type: String, required: true },
  cancelLabel: { type: String, required: true },
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <div v-if="open" class="app-modal-backdrop" @click.self="emit('cancel')">
    <div class="app-modal app-modal--confirm">
      <div class="app-modal__body app-modal__body--confirm">
        <div class="app-modal__confirm-icon">{{ icon }}</div>
        <h3>{{ title }}</h3>
        <p>{{ text }}</p>
      </div>
      <div class="app-modal__footer">
        <button type="button" class="btn btn--ghost" @click="emit('cancel')">{{ cancelLabel }}</button>
        <button type="button" class="btn btn--danger" @click="emit('confirm')">{{ confirmLabel }}</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.app-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(6px);
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
}

.app-modal--confirm {
  display: block;
  background: var(--panel-bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  width: 100%;
  max-width: 360px;
  text-align: center;
  box-shadow: var(--shadow);
}

.app-modal__body--confirm {
  padding: 28px 28px 0;
}
.app-modal__confirm-icon {
  font-size: 36px;
  margin: 8px 0 12px;
}
.app-modal__body--confirm h3 {
  font-size: 16px;
  font-weight: 800;
  margin-bottom: 6px;
}
.app-modal__body--confirm p {
  font-size: 13px;
  color: var(--text);
  opacity: 0.85;
  line-height: 1.5;
}

.app-modal__footer {
  padding: 16px 24px;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.btn--danger {
  background: #ef4444;
  color: #fff;
}
.btn--danger:hover {
  opacity: 0.88;
}
</style>
