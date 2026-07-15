<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const router = useRouter()
const loading = ref(true)

onMounted(async () => {
  try {
    if (!authStore.user) {
      await authStore.fetchMe()
    }
  } finally {
    loading.value = false
  }
})

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <main class="narrow-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Dashboard</h1>
      <button class="btn btn--ghost btn--sm" @click="handleLogout">Sair</button>
    </div>

    <p v-if="loading">Carregando...</p>

    <div v-else-if="authStore.user" class="panel p-3">
      <p class="mb-1"><strong>Nome:</strong> {{ authStore.user.name }}</p>
      <p class="mb-0"><strong>E-mail:</strong> {{ authStore.user.email }}</p>
    </div>
  </main>
</template>
