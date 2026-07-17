<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { strings } from '../strings/pt-BR'

const authStore = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errorMessage = ref('')
const loading = ref(false)

async function handleSubmit() {
  errorMessage.value = ''
  loading.value = true

  try {
    await authStore.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    router.push({ name: 'login' })
  } catch (error) {
    errorMessage.value = error.response?.data?.error?.message || strings.auth.register.genericError
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="narrow-page">
    <h1 class="h3 mb-4">{{ strings.auth.register.title }}</h1>

    <form @submit.prevent="handleSubmit">
      <div class="mb-3">
        <label for="name" class="form-label">{{ strings.auth.register.nameLabel }}</label>
        <input id="name" v-model="name" type="text" class="form-control" required autocomplete="name" />
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">{{ strings.auth.register.emailLabel }}</label>
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
        <label for="password" class="form-label">{{ strings.auth.register.passwordLabel }}</label>
        <input
          id="password"
          v-model="password"
          type="password"
          class="form-control"
          required
          autocomplete="new-password"
        />
      </div>

      <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{
          strings.auth.register.passwordConfirmationLabel
        }}</label>
        <input
          id="password_confirmation"
          v-model="passwordConfirmation"
          type="password"
          class="form-control"
          required
          autocomplete="new-password"
        />
      </div>

      <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>

      <button type="submit" class="btn btn--primary btn--block" :disabled="loading">
        {{ loading ? strings.auth.register.submitLoading : strings.auth.register.submit }}
      </button>
    </form>

    <p class="mt-3 text-center">
      {{ strings.auth.register.hasAccount }}
      <RouterLink to="/login">{{ strings.auth.register.loginLink }}</RouterLink>
    </p>
  </main>
</template>
