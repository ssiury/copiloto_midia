<script setup>
import { RouterView } from 'vue-router'
import ThemeToggle from './components/ThemeToggle.vue'
import Toast from './components/Toast.vue'
</script>

<template>
  <RouterView />
  <ThemeToggle />
  <Toast />
</template>
