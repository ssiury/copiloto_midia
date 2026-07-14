<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

async function handleSubmit() {
  errorMessage.value = ''
  loading.value = true

  try {
    await authStore.login({ email: email.value, password: password.value })
    router.push({ name: 'dashboard' })
  } catch (error) {
    errorMessage.value = error.response?.data?.error?.message || 'Não foi possível fazer login.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="narrow-page">
    <h1 class="h3 mb-4">Entrar</h1>

    <form @submit.prevent="handleSubmit">
      <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input
          id="email"
          v-model="email"
          type="email"
          class="form-control"
          required
          autocomplete="username"
        />
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <input
          id="password"
          v-model="password"
          type="password"
          class="form-control"
          required
          autocomplete="current-password"
        />
      </div>

      <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>

      <button type="submit" class="btn btn--primary btn--block" :disabled="loading">
        {{ loading ? 'Entrando...' : 'Entrar' }}
      </button>
    </form>

    <p class="mt-3 text-center">
      Não tem conta? <RouterLink to="/register">Cadastre-se</RouterLink>
    </p>
  </main>
</template>
