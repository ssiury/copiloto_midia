<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { strings } from '../strings/pt-BR'

const authStore = useAuthStore()

const firstName = computed(() => authStore.user?.name?.split(' ')[0] || '')

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return { text: strings.dashboard.greeting.morning, emoji: strings.dashboard.greeting.morningEmoji }
  if (hour < 18) {
    return { text: strings.dashboard.greeting.afternoon, emoji: strings.dashboard.greeting.afternoonEmoji }
  }
  return { text: strings.dashboard.greeting.evening, emoji: strings.dashboard.greeting.eveningEmoji }
})

const dateLabel = computed(() => {
  const formatted = new Intl.DateTimeFormat('pt-BR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(new Date())

  return formatted.charAt(0).toUpperCase() + formatted.slice(1)
})

// TODO: dados de placeholder até existirem endpoints reais de posts/agenda/aniversariantes.
const scheduledCount = 2
const birthdayCount = 1

const stats = [
  { icon: '📸', value: 12, key: 'postsThisMonth', color: 'accent' },
  { icon: '📅', value: 5, key: 'scheduled', color: 'gold' },
  { icon: '🎂', value: 4, key: 'birthdaysThisMonth', color: 'green' },
]

const birthdayToday = {
  name: 'Maria Santos',
  note: 'A arte e a legenda estão prontas para aprovação.',
}

const events = [
  {
    id: 1,
    title: 'Mensagem de Célula — Grupo da Igreja',
    subtitle: 'WhatsApp • Todo terça e quinta pela manhã',
    time: '08:00',
    tag: 'auto',
    color: 'green',
  },
  {
    id: 2,
    title: 'Arte de Aniversário — Maria Santos',
    subtitle: 'Story + Post + Grupo WhatsApp',
    time: '10:30',
    tag: 'wait',
    color: 'gold',
  },
  {
    id: 3,
    title: 'Post do Culto de Sexta',
    subtitle: 'Instagram Reel + Story',
    time: '20:00',
    tag: 'manual',
    color: 'accent',
  },
]
</script>

<template>
  <div v-if="authStore.user" class="greeting">
    <div class="greeting__eyebrow">
      <span class="live-dot" />
      {{ dateLabel }}
    </div>
    <h1 class="greeting__title">
      {{ greeting.text }}, <span class="greeting__name">{{ firstName }}</span> {{ greeting.emoji }}
    </h1>
    <p class="greeting__sub">
      {{ strings.dashboard.greeting.subtitlePrefix }}
      <strong>{{ strings.dashboard.greeting.subtitleScheduled(scheduledCount) }}</strong>
      {{ strings.dashboard.greeting.subtitleMiddle }}
      <strong class="text-gold">{{ strings.dashboard.greeting.subtitleBirthdays(birthdayCount) }}</strong
      >{{ strings.dashboard.greeting.subtitleSuffix }}
    </p>
  </div>

  <div class="summary-row">
    <div v-for="stat in stats" :key="stat.key" class="sum-card panel">
      <div class="sum-card__icon" :class="`sum-card__icon--${stat.color}`">{{ stat.icon }}</div>
      <div>
        <div class="sum-card__value" :class="`text-${stat.color}`">{{ stat.value }}</div>
        <div class="sum-card__label">{{ strings.dashboard.stats[stat.key] }}</div>
      </div>
    </div>
  </div>

  <div v-if="birthdayToday" class="bday-banner">
    <div class="bday-banner__emoji">🎂</div>
    <div class="bday-banner__text">
      <strong>{{ birthdayToday.name }} {{ strings.dashboard.birthdayBanner.titleSuffix }}</strong>
      <p>{{ birthdayToday.note }}</p>
    </div>
    <button type="button" class="btn btn--gold">{{ strings.dashboard.birthdayBanner.cta }}</button>
  </div>

  <div class="divider" />

  <div class="section-label">{{ strings.dashboard.agenda.sectionTitle }}</div>
  <div class="event-list">
    <div v-for="event in events" :key="event.id" class="event-item panel">
      <div class="event-item__dot" :class="`event-item__dot--${event.color}`" />
      <div class="event-item__info">
        <strong>{{ event.title }}</strong>
        <span>{{ event.subtitle }}</span>
      </div>
      <span class="tag" :class="`tag--${event.tag}`">{{ strings.dashboard.agenda.tags[event.tag] }}</span>
      <div class="event-item__time">{{ event.time }}</div>
    </div>
  </div>
</template>

<style scoped>
/* ---------- Greeting ---------- */
.greeting {
  margin-bottom: 36px;
}
.greeting__eyebrow {
  font-size: 12px;
  font-weight: 500;
  color: var(--text);
  opacity: 0.75;
  letter-spacing: 0.04em;
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.live-dot {
  width: 6px;
  height: 6px;
  background: var(--green);
  border-radius: 50%;
  display: inline-block;
  animation: pulse 2.2s ease-in-out infinite;
}
@keyframes pulse {
  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.4;
    transform: scale(0.85);
  }
}
.greeting__title {
  font-size: 30px;
  font-weight: 800;
  line-height: 1.15;
  letter-spacing: -0.02em;
  margin: 0;
}
.greeting__name {
  color: var(--accent);
}
.greeting__sub {
  margin-top: 8px;
  font-size: 14px;
  color: var(--text);
  opacity: 0.85;
  line-height: 1.6;
}
.greeting__sub strong {
  color: var(--text-h);
}
.text-gold {
  color: var(--gold) !important;
}

/* ---------- Divider ---------- */
.divider {
  height: 1px;
  background: var(--border);
  margin: 28px 0;
}

/* ---------- Summary cards ---------- */
.summary-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
  margin-bottom: 28px;
}
.sum-card {
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.18s;
}
.sum-card:hover {
  border-color: var(--accent-border);
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}
.sum-card__icon {
  width: 42px;
  height: 42px;
  border-radius: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
  background: var(--accent-bg);
}
.sum-card__icon--gold {
  background: var(--gold-dim);
}
.sum-card__icon--green {
  background: var(--green-dim);
}
.sum-card__value {
  font-size: 24px;
  font-weight: 800;
  line-height: 1;
  color: var(--accent);
}
.text-green {
  color: var(--green) !important;
}
.sum-card__label {
  font-size: 12px;
  color: var(--text);
  opacity: 0.7;
  margin-top: 3px;
}

/* ---------- Birthday banner ---------- */
.bday-banner {
  background: var(--gold-dim);
  border: 1px solid rgba(245, 158, 11, 0.35);
  border-radius: 12px;
  padding: 18px 22px;
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
}
.bday-banner__emoji {
  font-size: 30px;
}
.bday-banner__text {
  flex: 1;
}
.bday-banner__text strong {
  font-size: 14px;
  color: var(--gold);
  display: block;
  margin-bottom: 3px;
}
.bday-banner__text p {
  font-size: 12px;
  color: var(--text);
  opacity: 0.85;
}
.btn--gold {
  background: var(--gold);
  color: #1a1000;
  white-space: nowrap;
}
.btn--gold:hover {
  box-shadow: var(--shadow);
}

/* ---------- Agenda ---------- */
.section-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text);
  opacity: 0.7;
  margin-bottom: 14px;
}
.event-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.event-item {
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.15s;
}
.event-item:hover {
  border-color: var(--accent-border);
}
.event-item__dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  flex-shrink: 0;
  background: var(--accent);
}
.event-item__dot--gold {
  background: var(--gold);
}
.event-item__dot--green {
  background: var(--green);
}
.event-item__info {
  flex: 1;
  display: flex;
  flex-direction: column;
}
.event-item__info strong {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--text-h);
}
.event-item__info span {
  font-size: 12px;
  color: var(--text);
  opacity: 0.7;
  margin-top: 1px;
}
.event-item__time {
  font-size: 12px;
  color: var(--text);
  opacity: 0.85;
  white-space: nowrap;
}
.tag {
  font-size: 10px;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}
.tag--auto {
  background: var(--green-dim);
  color: var(--green);
}
.tag--manual {
  background: var(--accent-bg);
  color: var(--accent);
}
.tag--wait {
  background: var(--gold-dim);
  color: var(--gold);
}

/* ---------- Responsive ---------- */
@media (max-width: 860px) {
  .summary-row {
    grid-template-columns: 1fr;
  }
}
</style>
