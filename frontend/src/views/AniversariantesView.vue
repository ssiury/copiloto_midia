<script setup>
import { computed, onMounted, ref } from 'vue'
import { strings } from '../strings/pt-BR'
import { useToastStore } from '../stores/toast'
import { useMembrosStore } from '../stores/membros'
import MembroCard from '../components/MembroCard.vue'
import MembroFormModal from '../components/MembroFormModal.vue'
import ConfirmDialog from '../components/ConfirmDialog.vue'
import {
  diasAteAniversario,
  isAniversarioHoje,
  isAniversarioNoMes,
  mesDoAniversario,
} from '../utils/aniversariantes'

const toastStore = useToastStore()
const membrosStore = useMembrosStore()

const hoje = new Date()
const erroAoCarregar = ref(false)

onMounted(async () => {
  try {
    erroAoCarregar.value = false
    await membrosStore.fetchMembros()
  } catch {
    erroAoCarregar.value = true
    toastStore.show(strings.aniversariantes.loadError, '⚠️')
  }
})

const membros = computed(() => membrosStore.membros)

const busca = ref('')
const filtro = ref('todos')

const FILTROS = {
  todos: (m) => m.ativo,
  hoje: (m) => m.ativo && isAniversarioHoje(m.data_nascimento),
  mes: (m) => m.ativo && isAniversarioNoMes(m.data_nascimento),
  inativos: (m) => !m.ativo,
}

const listaFiltrada = computed(() => {
  const termo = busca.value.toLowerCase().trim()
  let lista = membros.value.filter(FILTROS[filtro.value])

  if (termo) {
    lista = lista.filter((m) => m.nome.toLowerCase().includes(termo))
  }

  return [...lista].sort(
    (a, b) => diasAteAniversario(a.data_nascimento) - diasAteAniversario(b.data_nascimento),
  )
})

const gruposPorMes = computed(() => {
  if (!['todos', 'mes', 'inativos'].includes(filtro.value)) return null

  const grupos = new Map()
  listaFiltrada.value.forEach((membro) => {
    const mes = mesDoAniversario(membro.data_nascimento)
    if (!grupos.has(mes)) grupos.set(mes, [])
    grupos.get(mes).push(membro)
  })

  if (grupos.size <= 1) return null

  const mesAtual = hoje.getMonth()
  const formatter = new Intl.DateTimeFormat('pt-BR', { month: 'long' })

  return [...grupos.keys()]
    .sort((a, b) => ((a - mesAtual + 12) % 12) - ((b - mesAtual + 12) % 12))
    .map((mes) => ({
      mes,
      label: formatter.format(new Date(2000, mes, 1)),
      membros: grupos.get(mes),
    }))
})

const totalAtivos = computed(() => membros.value.filter((m) => m.ativo).length)

const modalFormAberto = ref(false)
const membroEditando = ref(null)

function abrirCriar() {
  membroEditando.value = null
  modalFormAberto.value = true
}

function abrirEditar(membro) {
  membroEditando.value = membro
  modalFormAberto.value = true
}

async function salvarMembro(dados) {
  try {
    if (membroEditando.value) {
      await membrosStore.atualizar(membroEditando.value.id, dados)
      toastStore.show(strings.aniversariantes.toasts.atualizado, '✅')
    } else {
      await membrosStore.criar(dados)
      toastStore.show(strings.aniversariantes.toasts.cadastrado, '🎉')
    }

    modalFormAberto.value = false
  } catch (error) {
    toastStore.show(error.response?.data?.error?.message || strings.aniversariantes.toasts.erroSalvar, '⚠️')
  }
}

const modalConfirmAberto = ref(false)
const membroDesativando = ref(null)

function pedirDesativar(membro) {
  membroDesativando.value = membro
  modalConfirmAberto.value = true
}

async function confirmarDesativar() {
  try {
    await membrosStore.desativar(membroDesativando.value.id)
    toastStore.show(strings.aniversariantes.toasts.desativado, '🔕')
  } catch (error) {
    toastStore.show(error.response?.data?.error?.message || strings.aniversariantes.toasts.erroDesativar, '⚠️')
  } finally {
    modalConfirmAberto.value = false
  }
}
</script>

<template>
  <div class="page-header">
    <div>
      <h1>{{ strings.aniversariantes.pageTitle }}</h1>
      <p class="page-header__subtitle">{{ strings.aniversariantes.subtitle(totalAtivos) }}</p>
    </div>
    <button type="button" class="btn btn--primary" @click="abrirCriar">
      <i class="bi bi-plus-lg" aria-hidden="true"></i> {{ strings.aniversariantes.cadastrarBtn }}
    </button>
  </div>

  <div class="filters">
    <div class="filters__search">
      <i class="bi bi-search" aria-hidden="true"></i>
      <input v-model="busca" type="text" :placeholder="strings.aniversariantes.searchPlaceholder" />
    </div>
    <button
      v-for="opcao in ['todos', 'hoje', 'mes', 'inativos']"
      :key="opcao"
      type="button"
      class="filter-btn"
      :class="{ 'filter-btn--active': filtro === opcao }"
      @click="filtro = opcao"
    >
      {{ strings.aniversariantes.filtros[opcao] }}
    </button>
    <span v-if="listaFiltrada.length" class="filters__count">{{
      strings.aniversariantes.resultsCount(listaFiltrada.length)
    }}</span>
  </div>

  <p v-if="membrosStore.carregando && membros.length === 0" class="loading-state">
    {{ strings.aniversariantes.loading }}
  </p>

  <div v-else-if="listaFiltrada.length === 0" class="empty-state">
    <div class="empty-state__icon">🎂</div>
    <h3>{{ strings.aniversariantes.emptyState.title }}</h3>
    <p>{{ strings.aniversariantes.emptyState.text }}</p>
    <button type="button" class="btn btn--primary" @click="abrirCriar">
      {{ strings.aniversariantes.cadastrarPrimeiroBtn }}
    </button>
  </div>

  <template v-else-if="gruposPorMes">
    <div v-for="grupo in gruposPorMes" :key="grupo.mes" class="month-group">
      <div class="month-group__label">{{ grupo.label }}</div>
      <div class="members-grid">
        <MembroCard
          v-for="membro in grupo.membros"
          :key="membro.id"
          :membro="membro"
          @editar="abrirEditar"
          @desativar="pedirDesativar"
        />
      </div>
    </div>
  </template>

  <div v-else class="members-grid">
    <MembroCard
      v-for="membro in listaFiltrada"
      :key="membro.id"
      :membro="membro"
      @editar="abrirEditar"
      @desativar="pedirDesativar"
    />
  </div>

  <MembroFormModal
    :open="modalFormAberto"
    :membro="membroEditando"
    @close="modalFormAberto = false"
    @save="salvarMembro"
  />

  <ConfirmDialog
    :open="modalConfirmAberto"
    icon="🗑️"
    :title="strings.aniversariantes.modalConfirm.titulo"
    :text="strings.aniversariantes.modalConfirm.texto"
    :confirm-label="strings.aniversariantes.modalConfirm.confirmar"
    :cancel-label="strings.aniversariantes.modalConfirm.cancelar"
    @confirm="confirmarDesativar"
    @cancel="modalConfirmAberto = false"
  />
</template>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  gap: 16px;
}
.page-header h1 {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin: 0;
}
.page-header__subtitle {
  font-size: 13px;
  color: var(--text);
  opacity: 0.7;
  margin-top: 3px;
}

.filters {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.filters__search {
  position: relative;
  flex: 1;
  max-width: 320px;
  display: flex;
  align-items: center;
}
.filters__search i {
  position: absolute;
  left: 12px;
  font-size: 13px;
  color: var(--text);
  opacity: 0.6;
  pointer-events: none;
}
.filters__search input {
  width: 100%;
  background: var(--panel-bg);
  border: 1px solid var(--border);
  border-radius: 9px;
  padding: 9px 12px 9px 34px;
  color: var(--text-h);
  font-size: 13px;
  font-family: inherit;
  outline: none;
  transition: border 0.15s;
}
.filters__search input:focus {
  border-color: var(--accent);
}
.filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--panel-bg);
  border: 1px solid var(--border);
  border-radius: 9px;
  padding: 9px 14px;
  color: var(--text);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}
.filter-btn:hover {
  color: var(--text-h);
  border-color: var(--accent-border);
}
.filter-btn--active {
  background: var(--accent-bg);
  border-color: var(--accent-border);
  color: var(--accent);
}
.filters__count {
  font-size: 12px;
  color: var(--text);
  opacity: 0.6;
  margin-left: auto;
  white-space: nowrap;
}

.month-group {
  margin-bottom: 28px;
}
.month-group__label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.09em;
  color: var(--text);
  opacity: 0.6;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.month-group__label::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}

.members-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 12px;
}

.loading-state {
  text-align: center;
  padding: 80px 20px;
  color: var(--text);
  opacity: 0.7;
  font-size: 13px;
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
}
.empty-state__icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}
.empty-state h3 {
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 6px;
}
.empty-state p {
  font-size: 13px;
  color: var(--text);
  opacity: 0.7;
  margin-bottom: 20px;
}

@media (max-width: 860px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .filters__search {
    max-width: none;
    width: 100%;
  }
}

@media (max-width: 480px) {
  .members-grid {
    grid-template-columns: 1fr;
  }
}
</style>
