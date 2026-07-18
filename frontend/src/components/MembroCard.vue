<script setup>
import { computed } from 'vue'
import { strings } from '../strings/pt-BR'
import {
  corAvatar,
  diasAteAniversario,
  formatarDataNascimento,
  idadeAtual,
  iniciaisNome,
  isAniversarioHoje,
} from '../utils/aniversariantes'

const props = defineProps({
  membro: { type: Object, required: true },
})

defineEmits(['editar', 'desativar'])

const dias = computed(() => diasAteAniversario(props.membro.data_nascimento))
const ehHoje = computed(() => isAniversarioHoje(props.membro.data_nascimento))
const cor = computed(() => corAvatar(props.membro.nome))

const countdown = computed(() => {
  if (ehHoje.value) {
    return { classe: 'countdown--hoje', texto: strings.aniversariantes.card.hojeBadge }
  }
  if (dias.value <= 7) {
    return { classe: 'countdown--breve', texto: strings.aniversariantes.card.emDias(dias.value) }
  }
  return { classe: 'countdown--distante', texto: strings.aniversariantes.card.emDias(dias.value) }
})

const dataFormatada = computed(() =>
  formatarDataNascimento(props.membro.data_nascimento, props.membro.ignorar_ano),
)

const idadeTexto = computed(() =>
  props.membro.ignorar_ano
    ? ''
    : ` • ${strings.aniversariantes.card.anos(idadeAtual(props.membro.data_nascimento))}`,
)
</script>

<template>
  <div class="member-card" :class="{ 'member-card--hoje': ehHoje }">
    <div
      class="member-card__avatar"
      :style="{ background: `linear-gradient(135deg, ${cor[0]}, ${cor[1]})` }"
    >
      <img v-if="membro.foto" :src="`data:${membro.foto_tipo};base64,${membro.foto}`" :alt="membro.nome" />
      <span v-else>{{ iniciaisNome(membro.nome) }}</span>
      <div v-if="ehHoje" class="member-card__today-badge">🎂</div>
    </div>

    <div class="member-card__info">
      <div class="member-card__name">
        {{ membro.nome }}
        <span v-if="!membro.ativo" class="member-card__inactive-badge">{{
          strings.aniversariantes.card.inativoBadge
        }}</span>
      </div>
      <div class="member-card__date">{{ dataFormatada }}{{ idadeTexto }}</div>
      <div class="member-card__countdown" :class="countdown.classe">{{ countdown.texto }}</div>
    </div>

    <div class="member-card__actions">
      <button
        type="button"
        class="member-card__act"
        :title="strings.aniversariantes.card.editar"
        @click="$emit('editar', membro)"
      >
        <i class="bi bi-pencil" aria-hidden="true"></i>
      </button>
      <button
        type="button"
        class="member-card__act member-card__act--danger"
        :title="strings.aniversariantes.card.desativar"
        @click="$emit('desativar', membro)"
      >
        <i class="bi bi-trash" aria-hidden="true"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.member-card {
  background: var(--panel-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.18s;
  position: relative;
  overflow: hidden;
}
.member-card:hover {
  border-color: var(--accent-border);
  transform: translateY(-1px);
  box-shadow: var(--shadow);
}
.member-card--hoje {
  border-color: rgba(245, 158, 11, 0.35);
  background: var(--gold-dim);
}
.member-card--hoje::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, var(--gold), transparent);
}

.member-card__avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 17px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
  position: relative;
}
.member-card__avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}
.member-card__today-badge {
  position: absolute;
  bottom: -2px;
  right: -2px;
  background: var(--gold);
  border-radius: 50%;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  border: 2px solid var(--panel-bg);
}

.member-card__info {
  flex: 1;
  min-width: 0;
}
.member-card__name {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-h);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.member-card__inactive-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 20px;
  background: var(--border);
  color: var(--text);
  margin-left: 6px;
}
.member-card__date {
  font-size: 12px;
  color: var(--text);
  opacity: 0.75;
  margin-top: 2px;
}
.member-card__countdown {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 20px;
  margin-top: 6px;
}
.countdown--hoje {
  background: var(--gold-dim);
  color: var(--gold);
}
.countdown--breve {
  background: var(--green-dim);
  color: var(--green);
}
.countdown--distante {
  background: var(--border);
  color: var(--text);
}

.member-card__actions {
  display: flex;
  flex-direction: column;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.15s;
}
/* Só esconde por padrão em dispositivos com hover real (mouse). Em touch
   (sem :hover) os botões ficam sempre visíveis — senão a ação vira
   inacessível no celular. */
@media (hover: hover) and (pointer: fine) {
  .member-card:hover .member-card__actions {
    opacity: 1;
  }
}
@media (hover: none), (pointer: coarse) {
  .member-card__actions {
    opacity: 1;
  }
}
.member-card__act {
  width: 30px;
  height: 30px;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: 7px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  transition: all 0.15s;
  color: var(--text);
}
.member-card__act:hover {
  background: var(--accent-bg);
  color: var(--text-h);
  border-color: var(--accent-border);
}
.member-card__act--danger:hover {
  background: rgba(239, 68, 68, 0.12);
  color: #ef4444;
  border-color: rgba(239, 68, 68, 0.4);
}
</style>
