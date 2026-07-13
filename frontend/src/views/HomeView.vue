<script setup>
import { ref, onMounted } from 'vue'
import http from '../services/http'

const status = ref('carregando...')
const error = ref(null)

onMounted(async () => {
  try {
    const { data } = await http.get('/v1/health')
    status.value = data.data.status
  } catch (e) {
    error.value = e.message
  }
})
</script>

<template>
  <main class="container py-5">
    <h1>copiloto_midia</h1>
    <p>Status da API: <strong>{{ error ? 'erro' : status }}</strong></p>
    <p v-if="error" class="text-danger">{{ error }}</p>
  </main>
</template>
