import { defineStore } from 'pinia'
import http from '../services/http'

// A API devolve `data_nascimento` como ISO completo
// ("1999-03-10T00:00:00.000000Z"). O resto do front (cards, cálculo de
// dias até o aniversário, <input type="date"> do formulário) espera só
// "YYYY-MM-DD" — normaliza uma vez aqui pra ninguém mais precisar tratar.
function normalizarMembro(membro) {
  return {
    ...membro,
    data_nascimento: membro.data_nascimento ? membro.data_nascimento.slice(0, 10) : membro.data_nascimento,
  }
}

export const useMembrosStore = defineStore('membros', {
  state: () => ({
    membros: [],
    carregando: false,
  }),

  actions: {
    async fetchMembros() {
      this.carregando = true

      try {
        const { data } = await http.get('/v1/membros')
        this.membros = data.data.map(normalizarMembro)

        return this.membros
      } finally {
        this.carregando = false
      }
    },

    async criar(dados) {
      const { data } = await http.post('/v1/membros', dados)

      // A API nunca devolve `foto` em base64 (evita inflar o payload — ver
      // docs/midia-igreja.md). Guardamos a que acabamos de enviar só pra
      // manter o preview visível nesta sessão.
      const membro = normalizarMembro({
        ...data.data,
        foto: dados.foto ?? null,
        foto_tipo: dados.foto_tipo ?? null,
        foto_nome: dados.foto_nome ?? null,
      })

      this.membros.push(membro)

      return membro
    },

    async atualizar(id, dados) {
      const existente = this.membros.find((m) => m.id === id)
      const { data } = await http.put(`/v1/membros/${id}`, dados)

      const membro = normalizarMembro({
        ...data.data,
        foto: dados.foto ?? existente?.foto ?? null,
        foto_tipo: dados.foto_tipo ?? existente?.foto_tipo ?? null,
        foto_nome: dados.foto_nome ?? existente?.foto_nome ?? null,
      })

      const idx = this.membros.findIndex((m) => m.id === id)
      if (idx !== -1) this.membros[idx] = membro

      return membro
    },

    async desativar(id) {
      const existente = this.membros.find((m) => m.id === id)
      const { data } = await http.delete(`/v1/membros/${id}`)

      const membro = normalizarMembro({
        ...data.data,
        foto: existente?.foto ?? null,
        foto_tipo: existente?.foto_tipo ?? null,
        foto_nome: existente?.foto_nome ?? null,
      })

      const idx = this.membros.findIndex((m) => m.id === id)
      if (idx !== -1) this.membros[idx] = membro

      return membro
    },
  },
})
