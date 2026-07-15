<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useSubscriptionStore } from '../stores/subscription'

const authStore = useAuthStore()
const subscriptionStore = useSubscriptionStore()
const router = useRouter()
const loading = ref(true)

onMounted(async () => {
  try {
    if (!authStore.user) {
      await authStore.fetchMe()
    }

    await subscriptionStore.fetchSubscription()
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

    <template v-else>
      <div v-if="authStore.user" class="panel p-3 mb-3">
        <p class="mb-1"><strong>Nome:</strong> {{ authStore.user.name }}</p>
        <p class="mb-1"><strong>E-mail:</strong> {{ authStore.user.email }}</p>
        <p class="mb-0"><strong>Tipo de usuário:</strong> {{ authStore.user.user_type }}</p>
      </div>

      <div v-if="subscriptionStore.subscription" class="panel p-3">
        <h2 class="h5 mb-3">Plano e limites</h2>
        <p class="mb-1"><strong>Plano atual:</strong> {{ subscriptionStore.subscription.plan.name }}</p>
        <p class="mb-3">
          <strong>Status da assinatura:</strong> {{ subscriptionStore.subscription.status }}
        </p>

        <p v-if="subscriptionStore.subscription.plan.is_unlimited" class="mb-0 text-muted">
          Este plano não possui limites de uso.
        </p>

        <ul v-else class="list-unstyled mb-0">
          <li
            v-for="limit in subscriptionStore.subscription.limits"
            :key="limit.resource"
            class="d-flex justify-content-between border-bottom py-2"
          >
            <span class="text-capitalize">{{ limit.resource }}</span>
            <span>{{ limit.used }} / {{ limit.unlimited ? '∞' : limit.limit }}</span>
          </li>
        </ul>
      </div>
    </template>
  </main>
</template>
