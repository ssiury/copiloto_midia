<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { strings } from '../strings/pt-BR'

const authStore = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}

const navItems = [
  { icon: 'bi-speedometer2', key: 'dashboard', to: { name: 'dashboard' } },
  { icon: 'bi-cake2-fill', key: 'birthdays', badge: 1 },
  { icon: 'bi-pencil-square', key: 'captions' },
  { icon: 'bi-gear-fill', key: 'settings' },
]

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
</script>

<template>
  <aside class="sidebar">
    <div class="sidebar__logo">
      <div class="sidebar__logo-mark"><i class="bi bi-stars" aria-hidden="true"></i></div>
      <div>
        <div class="sidebar__logo-name">{{ strings.common.brand }}</div>
        <div class="sidebar__logo-sub">{{ strings.dashboard.logoSub }}</div>
      </div>
    </div>

    <nav class="sidebar__nav">
      <RouterLink
        v-for="item in navItems.filter((i) => i.to)"
        :key="item.key"
        :to="item.to"
        class="nav-item"
        active-class="nav-item--active"
      >
        <i class="nav-item__icon bi" :class="item.icon" aria-hidden="true"></i>
        {{ strings.dashboard.nav[item.key] }}
        <span v-if="item.badge" class="nav-item__badge">{{ item.badge }}</span>
      </RouterLink>
      <span
        v-for="item in navItems.filter((i) => !i.to)"
        :key="item.key"
        class="nav-item nav-item--disabled"
        :title="strings.dashboard.nav.comingSoon"
      >
        <i class="nav-item__icon bi" :class="item.icon" aria-hidden="true"></i>
        {{ strings.dashboard.nav[item.key] }}
        <span v-if="item.badge" class="nav-item__badge">{{ item.badge }}</span>
      </span>
    </nav>

    <button type="button" class="sidebar__footer" :title="strings.dashboard.logout">
      <span class="avatar">{{ initials }}</span>
      <span class="sidebar__footer-info">
        <span class="sidebar__footer-name">{{ authStore.user?.name }}</span>
        <span class="sidebar__footer-role">{{ roleLabel }}</span>
      </span>
      <i class="sidebar__footer-logout bi bi-box-arrow-right" aria-hidden="true" @click="handleLogout"></i>
    </button>
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
}

.sidebar__logo {
  padding: 26px 22px 22px;
  display: flex;
  align-items: center;
  gap: 11px;
  border-bottom: 1px solid var(--border);
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
  cursor: pointer;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  transition: background 0.15s;
}
.sidebar__footer:hover {
  background: var(--accent-bg);
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
  font-size: 15px;
  color: var(--text);
  opacity: 0.6;
  cursor: pointer;
  transition:
    color 0.15s,
    opacity 0.15s;
}
.sidebar__footer-logout:hover {
  color: #ef4444;
  opacity: 1;
}

/* ---------- Responsive ---------- */
@media (max-width: 860px) {
  .sidebar {
    position: static;
    width: 100%;
    min-height: auto;
    flex-direction: row;
    align-items: center;
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
