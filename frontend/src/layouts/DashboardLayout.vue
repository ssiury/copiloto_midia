<script setup>
import { onMounted, ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useSidebarStore } from '../stores/sidebar'
import Sidebar from '../components/Sidebar.vue'
import { strings } from '../strings/pt-BR'

const authStore = useAuthStore()
const sidebarStore = useSidebarStore()
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
</script>

<template>
  <p v-if="loading" class="dashboard-layout__loading">{{ strings.dashboard.loading }}</p>

  <div v-else class="dashboard-layout" :class="{ 'dashboard-layout--collapsed': sidebarStore.collapsed }">
    <Sidebar />
    <main class="dashboard-layout__main">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.dashboard-layout {
  --gold: #f59e0b;
  --gold-dim: rgba(245, 158, 11, 0.12);
  --green: #10b981;
  --green-dim: rgba(16, 185, 129, 0.12);

  min-height: 100svh;
  display: flex;
}

.dashboard-layout__loading {
  padding: 40px 36px;
}

.dashboard-layout__main {
  margin-left: 220px;
  flex: 1;
  padding: 40px 36px;
  min-width: 0;
  transition: margin-left 0.2s ease;
}
.dashboard-layout--collapsed .dashboard-layout__main {
  margin-left: 72px;
}

@media (max-width: 860px) {
  .dashboard-layout,
  .dashboard-layout--collapsed {
    flex-direction: column;
  }
  .dashboard-layout__main,
  .dashboard-layout--collapsed .dashboard-layout__main {
    margin-left: 0;
    padding: 24px 20px;
    max-width: 100%;
  }
}
</style>
