<script setup>
import { ref, watch } from 'vue'
import { strings } from '../strings/pt-BR'
import { useToastStore } from '../stores/toast'

const props = defineProps({
  open: { type: Boolean, default: false },
  membro: { type: Object, default: null },
})

const emit = defineEmits(['close', 'save'])

const toastStore = useToastStore()
const t = strings.aniversariantes.modalForm

const nome = ref('')
const dataNascimento = ref('')
const whatsapp = ref('')
const observacoes = ref('')
const ignorarAno = ref(false)
const ativo = ref(true)
const foto = ref(null)
const fotoTipo = ref(null)
const fotoNome = ref(null)
const fotoPreviewUrl = ref(null)

function resetForm() {
  const editando = props.membro

  nome.value = editando?.nome ?? ''
  // A API devolve `data_nascimento` como ISO completo
  // ("1999-03-10T00:00:00.000000Z"); <input type="date"> só aceita
  // "YYYY-MM-DD" — sem isso o campo fica em branco sem erro nenhum.
  dataNascimento.value = editando?.data_nascimento ? editando.data_nascimento.slice(0, 10) : ''
  whatsapp.value = editando?.whatsapp ?? ''
  observacoes.value = editando?.observacoes ?? ''
  ignorarAno.value = editando?.ignorar_ano ?? false
  ativo.value = editando?.ativo ?? true
  foto.value = editando?.foto ?? null
  fotoTipo.value = editando?.foto_tipo ?? null
  fotoNome.value = editando?.foto_nome ?? null
  fotoPreviewUrl.value = editando?.foto ? `data:${editando.foto_tipo};base64,${editando.foto}` : null
}

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) resetForm()
  },
)

function selecionarFoto(event) {
  const arquivo = event.target.files[0]
  if (!arquivo) return

  fotoTipo.value = arquivo.type
  fotoNome.value = arquivo.name

  const leitor = new FileReader()
  leitor.onload = (ev) => {
    foto.value = ev.target.result.split(',')[1]
    fotoPreviewUrl.value = ev.target.result
  }
  leitor.readAsDataURL(arquivo)
}

function salvar() {
  if (!nome.value.trim()) {
    toastStore.show(strings.aniversariantes.toasts.nomeObrigatorio, '⚠️')
    return
  }
  if (!dataNascimento.value) {
    toastStore.show(strings.aniversariantes.toasts.dataObrigatoria, '⚠️')
    return
  }

  emit('save', {
    nome: nome.value.trim(),
    data_nascimento: dataNascimento.value,
    whatsapp: whatsapp.value.trim(),
    observacoes: observacoes.value.trim(),
    ignorar_ano: ignorarAno.value,
    ativo: ativo.value,
    foto: foto.value,
    foto_tipo: fotoTipo.value,
    foto_nome: fotoNome.value,
  })
}
</script>

<template>
  <div v-if="open" class="app-modal-backdrop" @click.self="emit('close')">
    <div class="app-modal">
      <div class="app-modal__header">
        <span class="app-modal__title">{{ membro ? t.tituloEditar : t.tituloCriar }}</span>
        <button type="button" class="app-modal__close" @click="emit('close')">
          <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
      </div>

      <div class="app-modal__body">
        <div class="foto-upload">
          <div class="foto-upload__preview">
            <img v-if="fotoPreviewUrl" :src="fotoPreviewUrl" alt="" />
            <i v-else class="bi bi-camera" aria-hidden="true"></i>
            <input type="file" accept="image/jpeg,image/png,image/webp" @change="selecionarFoto" />
          </div>
          <div class="foto-upload__info">
            <p>{{ t.fotoLabel }}</p>
            <span>{{ t.fotoHint }}</span>
          </div>
        </div>

        <div class="field">
          <label for="f-nome" class="form-label">{{ t.nomeLabel }}</label>
          <input id="f-nome" v-model="nome" type="text" class="form-control" :placeholder="t.nomePlaceholder" />
        </div>

        <div class="field-row">
          <div class="field">
            <label for="f-data" class="form-label">{{ t.dataLabel }}</label>
            <input id="f-data" v-model="dataNascimento" type="date" class="form-control" />
          </div>
          <div class="field">
            <label for="f-whatsapp" class="form-label">{{ t.whatsappLabel }}</label>
            <input
              id="f-whatsapp"
              v-model="whatsapp"
              type="text"
              class="form-control"
              :placeholder="t.whatsappPlaceholder"
            />
          </div>
        </div>

        <div class="toggle-row">
          <div>
            <div class="toggle-row__label">{{ t.ignorarAnoLabel }}</div>
            <div class="toggle-row__hint">{{ t.ignorarAnoHint }}</div>
          </div>
          <button
            type="button"
            role="switch"
            class="toggle"
            :class="{ 'toggle--on': ignorarAno }"
            :aria-checked="ignorarAno"
            @click="ignorarAno = !ignorarAno"
          ></button>
        </div>

        <div class="toggle-row">
          <div>
            <div class="toggle-row__label">{{ t.ativoLabel }}</div>
            <div class="toggle-row__hint">{{ t.ativoHint }}</div>
          </div>
          <button
            type="button"
            role="switch"
            class="toggle"
            :class="{ 'toggle--on': ativo }"
            :aria-checked="ativo"
            @click="ativo = !ativo"
          ></button>
        </div>

        <div class="field">
          <label for="f-obs" class="form-label">{{ t.obsLabel }}</label>
          <textarea id="f-obs" v-model="observacoes" class="form-control" :placeholder="t.obsPlaceholder"></textarea>
        </div>
      </div>

      <div class="app-modal__footer">
        <button type="button" class="btn btn--ghost" @click="emit('close')">{{ t.cancelar }}</button>
        <button type="button" class="btn btn--primary" @click="salvar">
          <i class="bi bi-check-lg" aria-hidden="true"></i> {{ t.salvar }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.app-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(6px);
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
}

.app-modal {
  display: block;
  background: var(--panel-bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: var(--shadow);
}

.app-modal__header {
  padding: 22px 24px 18px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  background: var(--panel-bg);
  z-index: 1;
}
.app-modal__title {
  font-size: 16px;
  font-weight: 800;
  color: var(--text-h);
}
.app-modal__close {
  width: 30px;
  height: 30px;
  background: var(--border);
  border: none;
  border-radius: 7px;
  cursor: pointer;
  color: var(--text);
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.app-modal__close:hover {
  color: var(--text-h);
}

.app-modal__body {
  padding: 22px 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.foto-upload {
  display: flex;
  align-items: center;
  gap: 16px;
}
.foto-upload__preview {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: var(--bg);
  border: 2px dashed var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  color: var(--text);
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.15s;
  overflow: hidden;
  position: relative;
}
.foto-upload__preview:hover {
  border-color: var(--accent);
  background: var(--accent-bg);
}
.foto-upload__preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}
.foto-upload__preview input[type='file'] {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
}
.foto-upload__info p {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-h);
  margin-bottom: 4px;
}
.foto-upload__info span {
  font-size: 11px;
  color: var(--text);
  opacity: 0.7;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.field .form-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text);
  opacity: 0.85;
  margin-bottom: 0;
}
.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 9px;
  padding: 12px 14px;
}
.toggle-row__label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-h);
}
.toggle-row__hint {
  font-size: 11px;
  color: var(--text);
  opacity: 0.7;
  margin-top: 1px;
}
.toggle {
  width: 38px;
  height: 22px;
  background: var(--border);
  border-radius: 20px;
  position: relative;
  cursor: pointer;
  transition: background 0.2s;
  flex-shrink: 0;
  border: none;
}
.toggle--on {
  background: var(--accent);
}
.toggle::after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: white;
  transition: transform 0.2s;
}
.toggle--on::after {
  transform: translateX(16px);
}

.app-modal__footer {
  padding: 16px 24px;
  border-top: 1px solid var(--border);
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  position: sticky;
  bottom: 0;
  background: var(--panel-bg);
}

@media (max-width: 480px) {
  .field-row {
    grid-template-columns: 1fr;
  }
}
</style>
