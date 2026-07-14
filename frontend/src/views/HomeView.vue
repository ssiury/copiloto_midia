<script setup>
import { ref, onMounted, computed } from 'vue'
import http from '../services/http'

const status = ref(null)
const error = ref(null)

const statusLabel = computed(() => {
  if (error.value) return 'API indisponível'
  if (!status.value) return 'Verificando...'
  return 'API online'
})

onMounted(async () => {
  try {
    const { data } = await http.get('/v1/health')
    status.value = data.data.status
  } catch (e) {
    error.value = e.message
  }
})

const features = [
  {
    icon: 'circle',
    title: 'Stories automáticos',
    text: 'Cria e publica stories a partir das suas fotos e vídeos, no melhor horário.',
  },
  {
    icon: 'message',
    title: 'WhatsApp integrado',
    text: 'Sugere e envia respostas e avisos direto pelo WhatsApp da equipe.',
  },
  {
    icon: 'grid',
    title: 'Cronograma inteligente',
    text: 'Organiza os avisos e publicações da semana e ajusta sozinho os horários.',
  },
  {
    icon: 'chart',
    title: 'Métricas em tempo real',
    text: 'Acompanha alcance e engajamento sem sair do painel.',
  },
]

const steps = [
  {
    number: 1,
    title: 'Conecte suas contas',
    text: 'Instagram, WhatsApp e o calendário do time em poucos cliques.',
  },
  {
    number: 2,
    title: 'Envie fotos e vídeos',
    text: 'O copiloto sugere legendas, formatos de story e os melhores horários.',
  },
  {
    number: 3,
    title: 'Publique com aprovação',
    text: 'O time revisa em um clique e tudo sai no horário certo.',
  },
]
</script>

<template>
  <div class="page">
    <header class="navbar">
      <div class="navbar__brand">
        <span class="navbar__dot" />
        Copiloto de Mídia
      </div>
      <nav class="navbar__links">
        <a href="#recursos">Recursos</a>
        <a href="#como-funciona">Como funciona</a>
      </nav>
      <div class="navbar__actions">
        <RouterLink to="/login" class="btn btn--ghost btn--sm">Entrar</RouterLink>
        <RouterLink to="/register" class="btn btn--primary btn--sm">Criar conta</RouterLink>
      </div>
    </header>

    <main class="hero">
      <div class="hero__text">
        <span class="badge" :class="{ 'badge--error': error, 'badge--pending': !status && !error }">
          <span class="badge__dot" />
          {{ status || error ? 'Copiloto ativo agora' : statusLabel }}
        </span>

        <h1 class="hero__title">Sua mídia social no piloto automático.</h1>
        <p class="hero__subtitle">
          Stories, mensagens de WhatsApp e o cronograma de publicações do seu
          time — organizados e sugeridos por IA, em um só lugar.
        </p>

        <div class="hero__actions">
          <RouterLink to="/register" class="btn btn--primary">Começar agora</RouterLink>
          <a href="#como-funciona" class="btn btn--ghost">Ver como funciona</a>
        </div>

        <p class="hero__note">USADO PELOS TIMES DE MARKETING E CONTEÚDO</p>
      </div>

      <div class="hero__preview panel" aria-hidden="true">
        <div class="preview__dots">
          <span /><span /><span />
        </div>

        <div class="preview__row">
          <span class="preview__avatar" />
          <div>
            <p class="preview__row-title">WhatsApp · Equipe Sul</p>
            <p class="preview__row-sub">Sugestão de resposta enviada ✓</p>
          </div>
          <span class="preview__status" />
        </div>

        <p class="preview__label">STORIES AGENDADOS</p>
        <div class="preview__stories">
          <span class="preview__story" />
          <span class="preview__story" />
          <span class="preview__story preview__story--empty" />
        </div>

        <div class="preview__event">
          <span class="preview__event-icon" />
          <div>
            <p class="preview__row-title">Hoje, 14:00 · Instagram</p>
            <p class="preview__row-sub">Cronograma sugerido pela IA</p>
          </div>
        </div>
      </div>
    </main>

    <section id="recursos" class="section">
      <h2 class="section__title">Tudo que seu time precisa</h2>
      <p class="section__subtitle">
        De stories a mensagens diretas, o copiloto cuida da rotina para o time
        focar no conteúdo.
      </p>

      <div class="features">
        <div v-for="f in features" :key="f.title" class="feature-card panel">
          <span class="feature-card__icon" :data-icon="f.icon" />
          <h3 class="feature-card__title">{{ f.title }}</h3>
          <p class="feature-card__text">{{ f.text }}</p>
        </div>
      </div>
    </section>

    <section id="como-funciona" class="section">
      <h2 class="section__title">Como funciona</h2>
      <p class="section__subtitle">
        Três passos para o time todo publicar sem esforço.
      </p>

      <div class="steps">
        <div v-for="s in steps" :key="s.number" class="step">
          <span class="step__number">{{ s.number }}</span>
          <h3 class="step__title">{{ s.title }}</h3>
          <p class="step__text">{{ s.text }}</p>
        </div>
      </div>
    </section>

    <footer class="footer">
      <span>Copiloto de Mídia</span>
      <span>© 2026 Copiloto de Mídia</span>
    </footer>
  </div>
</template>

<style scoped>
/*
 * Only page-specific layout lives here. Buttons, badges, panels, section
 * headers, navbar and footer come from the shared design system in
 * src/style.css — change those there, not per-page.
 */
.page {
  min-height: 100svh;
  display: flex;
  flex-direction: column;
}

/* ---------- Hero ---------- */
.hero {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: center;
  max-width: 1100px;
  margin: 0 auto;
  padding: 96px 48px;
  width: 100%;
}
.badge {
  margin-bottom: 24px;
}
.hero__title {
  font-size: 44px;
  line-height: 1.1;
  letter-spacing: -1.2px;
  margin: 0 0 16px;
}
.hero__subtitle {
  color: var(--text);
  font-size: 16px;
  line-height: 160%;
  margin-bottom: 28px;
  max-width: 440px;
}
.hero__actions {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}
.hero__note {
  font-size: 11px;
  letter-spacing: 0.06em;
  color: var(--text);
  opacity: 0.6;
}

/* ---------- Preview mockup ---------- */
.hero__preview {
  padding: 20px;
}
.preview__dots {
  display: flex;
  gap: 6px;
  margin-bottom: 16px;
}
.preview__dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--border);
}
.preview__row,
.preview__event {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 10px;
  padding: 12px 14px;
  margin-bottom: 16px;
}
.preview__avatar,
.preview__event-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--accent);
  flex-shrink: 0;
}
.preview__status {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #22c55e;
  margin-left: auto;
}
.preview__row-title {
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 2px;
}
.preview__row-sub {
  font-size: 12px;
  color: var(--text);
  opacity: 0.7;
}
.preview__label {
  font-size: 11px;
  letter-spacing: 0.06em;
  color: var(--text);
  opacity: 0.6;
  margin-bottom: 10px;
}
.preview__stories {
  display: flex;
  gap: 10px;
  margin-bottom: 16px;
}
.preview__story {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid var(--accent-border);
}
.preview__story--empty {
  border-style: dashed;
  opacity: 0.5;
}

/* ---------- Features grid ---------- */
.features {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  text-align: left;
}
.feature-card {
  padding: 22px;
}
.feature-card__icon {
  display: block;
  width: 34px;
  height: 34px;
  border-radius: 9px;
  background: var(--accent-bg);
  border: 1px solid var(--accent-border);
  margin-bottom: 16px;
}
.feature-card__title {
  font-size: 15px;
  margin-bottom: 8px;
}
.feature-card__text {
  font-size: 13.5px;
  color: var(--text);
  opacity: 0.75;
  line-height: 150%;
}

/* ---------- Steps ---------- */
.steps {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  position: relative;
}
.steps::before {
  content: '';
  position: absolute;
  top: 18px;
  left: 12%;
  right: 12%;
  height: 1px;
  background: var(--border);
  z-index: 0;
}
.step {
  position: relative;
  z-index: 1;
}
.step__number {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 18px;
}
.step__title {
  font-size: 15px;
  margin-bottom: 8px;
}
.step__text {
  font-size: 13.5px;
  color: var(--text);
  opacity: 0.75;
  line-height: 150%;
  max-width: 260px;
  margin: 0 auto;
}

/* ---------- Responsive ---------- */
@media (max-width: 860px) {
  .hero {
    grid-template-columns: 1fr;
    padding: 48px 24px;
  }
  .hero__title {
    font-size: 34px;
  }
  .features {
    grid-template-columns: repeat(2, 1fr);
  }
  .steps {
    grid-template-columns: 1fr;
  }
  .steps::before {
    display: none;
  }
}
</style>
