<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useSidebarStore } from '../stores/sidebar'
import { useMembrosStore } from '../stores/membros'
import { isAniversarioHoje } from '../utils/aniversariantes'
import { strings } from '../strings/pt-BR'

const authStore = useAuthStore()
const sidebarStore = useSidebarStore()
const membrosStore = useMembrosStore()
const router = useRouter()

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}

onMounted(() => {
  // Garante o badge certo mesmo se o usuário cair direto no Dashboard,
  // sem passar pela tela de Aniversariantes primeiro.
  membrosStore.fetchMembros()
})

const aniversariantesHojeCount = computed(
  () => membrosStore.membros.filter((m) => m.ativo && isAniversarioHoje(m.data_nascimento)).length,
)

const navItems = computed(() => [
  { icon: 'bi-speedometer2', key: 'dashboard', to: { name: 'dashboard' } },
  {
    icon: 'bi-cake2-fill',
    key: 'birthdays',
    to: { name: 'aniversariantes' },
    badge: aniversariantesHojeCount.value,
  },
  { icon: 'bi-pencil-square', key: 'captions' },
  { icon: 'bi-gear-fill', key: 'settings' },
])

const initials = computed(() =>
  (authStore.user?.name || '')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0].toUpperCase())
    .join(''),
)

const roleLabel = computed(
  () => strings.dashboard.roles[authStore.user?.user_type] || strings.dashboard.roles.fallback,
)

const toggleTitle = computed(() =>
  sidebarStore.collapsed ? strings.dashboard.expandSidebar : strings.dashboard.collapseSidebar,
)
</script>

<template>
  <aside class="sidebar" :class="{ 'sidebar--collapsed': sidebarStore.collapsed }">
    <div class="sidebar__logo">
      <div class="sidebar__logo-mark"><i class="bi bi-stars" aria-hidden="true"></i></div>
      <div v-if="!sidebarStore.collapsed" class="sidebar__logo-text">
        <div class="sidebar__logo-name">{{ strings.common.brand }}</div>
        <div class="sidebar__logo-sub">{{ strings.dashboard.logoSub }}</div>
      </div>
    </div>

    <button type="button" class="sidebar__toggle" :title="toggleTitle" :aria-label="toggleTitle" @click="sidebarStore.toggle()">
      <i class="bi" :class="sidebarStore.collapsed ? 'bi-chevron-right' : 'bi-chevron-left'" aria-hidden="true"></i>
    </button>

    <nav class="sidebar__nav">
      <RouterLink
        v-for="item in navItems.filter((i) => i.to)"
        :key="item.key"
        :to="item.to"
        class="nav-item"
        exact-active-class="nav-item--active"
        :title="sidebarStore.collapsed ? strings.dashboard.nav[item.key] : null"
      >
        <i class="nav-item__icon bi" :class="item.icon" aria-hidden="true"></i>
        <span v-if="!sidebarStore.collapsed" class="nav-item__label">{{ strings.dashboard.nav[item.key] }}</span>
        <span v-if="item.badge && !sidebarStore.collapsed" class="nav-item__badge">{{ item.badge }}</span>
      </RouterLink>
      <span
        v-for="item in navItems.filter((i) => !i.to)"
        :key="item.key"
        class="nav-item nav-item--disabled"
        :title="sidebarStore.collapsed ? strings.dashboard.nav[item.key] : strings.dashboard.nav.comingSoon"
      >
        <i class="nav-item__icon bi" :class="item.icon" aria-hidden="true"></i>
        <span v-if="!sidebarStore.collapsed" class="nav-item__label">{{ strings.dashboard.nav[item.key] }}</span>
        <span v-if="item.badge && !sidebarStore.collapsed" class="nav-item__badge">{{ item.badge }}</span>
      </span>
    </nav>

    <div class="sidebar__footer">
      <span class="avatar">{{ initials }}</span>
      <span v-if="!sidebarStore.collapsed" class="sidebar__footer-info">
        <span class="sidebar__footer-name">{{ authStore.user?.name }}</span>
        <span class="sidebar__footer-role">{{ roleLabel }}</span>
      </span>
      <button type="button" class="sidebar__footer-logout" :title="strings.dashboard.logout" @click="handleLogout">
        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
      </button>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  width: 220px;
  flex-shrink: 0;
  min-height: 100svh;
  background: var(--panel-bg);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  transition: width 0.2s ease;
}
.sidebar--collapsed {
  width: 72px;
}

.sidebar__logo {
  padding: 26px 22px 22px;
  display: flex;
  align-items: center;
  gap: 11px;
  border-bottom: 1px solid var(--border);
}
.sidebar--collapsed .sidebar__logo {
  padding: 26px 0 22px;
  justify-content: center;
}
.sidebar__logo-mark {
  width: 34px;
  height: 34px;
  background: var(--accent);
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  color: #fff;
  flex-shrink: 0;
}
.sidebar__logo-text {
  min-width: 0;
}
.sidebar__logo-name {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-h);
  line-height: 1.15;
}
.sidebar__logo-sub {
  font-size: 11px;
  color: var(--text);
  opacity: 0.7;
}

.sidebar__toggle {
  position: absolute;
  top: 32px;
  right: -12px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--panel-bg);
  border: 1px solid var(--border);
  color: var(--text);
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
  z-index: 2;
}
.sidebar__toggle:hover {
  color: var(--accent);
  border-color: var(--accent-border);
}

.sidebar__nav {
  flex: 1;
  padding: 20px 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 10px 12px;
  border-radius: 9px;
  color: var(--text);
  text-decoration: none;
  font-weight: 500;
  font-size: 13.5px;
  transition: all 0.15s;
}
.sidebar--collapsed .nav-item {
  justify-content: center;
  padding: 10px;
}
.nav-item--active,
.nav-item:not(.nav-item--disabled):hover {
  background: var(--accent-bg);
  color: var(--accent);
}
.nav-item--disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.nav-item__icon {
  font-size: 17px;
  width: 22px;
  text-align: center;
  flex-shrink: 0;
}
.nav-item__label {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.nav-item__badge {
  margin-left: auto;
  background: var(--gold);
  color: #1a1000;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 20px;
}

.sidebar__footer {
  padding: 14px 16px;
  border-top: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 10px;
}
.sidebar--collapsed .sidebar__footer {
  justify-content: center;
  padding: 14px 8px;
}
.avatar {
  width: 33px;
  height: 33px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  flex-shrink: 0;
}
.sidebar__footer-info {
  min-width: 0;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.sidebar__footer-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-h);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.sidebar__footer-role {
  font-size: 11px;
  color: var(--text);
  opacity: 0.7;
}
.sidebar__footer-logout {
  width: 26px;
  height: 26px;
  flex-shrink: 0;
  background: none;
  border: none;
  border-radius: 7px;
  font-size: 15px;
  color: var(--text);
  opacity: 0.6;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition:
    color 0.15s,
    opacity 0.15s,
    background 0.15s;
}
.sidebar__footer-logout:hover {
  color: #ef4444;
  opacity: 1;
  background: rgba(239, 68, 68, 0.1);
}

/* ---------- Responsive ---------- */
@media (max-width: 860px) {
  .sidebar,
  .sidebar--collapsed {
    position: static;
    width: 100%;
    min-height: auto;
    flex-direction: row;
    align-items: center;
  }
  .sidebar__toggle {
    display: none;
  }
  .sidebar__logo {
    border-bottom: none;
    border-right: 1px solid var(--border);
    padding: 14px 16px;
    min-width: 0;
  }
  .sidebar__logo-name,
  .sidebar__logo-sub {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .sidebar__nav {
    flex: 1;
    flex-direction: row;
    flex-wrap: nowrap;
    padding: 8px;
    overflow-x: auto;
  }
  .nav-item {
    white-space: nowrap;
    flex-shrink: 0;
  }
  .sidebar__footer {
    width: auto;
    flex-shrink: 0;
    border-top: none;
    border-left: 1px solid var(--border);
  }
  .sidebar__footer-info {
    display: none;
  }
}
</style>
