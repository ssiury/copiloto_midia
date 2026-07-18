// Helpers puros de data/apresentação para a tela de Aniversariantes — sem
// estado, sem I/O. Mesmo papel do `Services/` do backend.

const hoje = new Date()
hoje.setHours(0, 0, 0, 0)

// `new Date("YYYY-MM-DD")` (string sem horário) é interpretado como
// meia-noite UTC — em fusos negativos (ex.: Brasil, UTC-3) isso volta pro
// dia anterior ao ler de volta com getMonth()/getDate() (que são no fuso
// local). Forçar um horário (meio-dia, longe de qualquer virada de DST)
// faz o parser tratar como horário local, mantendo o dia certo.
function parseDataLocal(dataStr) {
  return new Date(`${dataStr}T12:00:00`)
}

export function mesDoAniversario(dataStr) {
  return parseDataLocal(dataStr).getMonth()
}

export function diasAteAniversario(dataStr) {
  const nascimento = parseDataLocal(dataStr)
  const aniversario = new Date(hoje.getFullYear(), nascimento.getMonth(), nascimento.getDate())

  if (aniversario < hoje) {
    aniversario.setFullYear(hoje.getFullYear() + 1)
  }

  return Math.round((aniversario - hoje) / 86400000)
}

export function idadeAtual(dataStr) {
  return hoje.getFullYear() - parseDataLocal(dataStr).getFullYear()
}

export function formatarDataNascimento(dataStr, ignorarAno) {
  const data = parseDataLocal(dataStr)
  const options = ignorarAno
    ? { day: '2-digit', month: 'long' }
    : { day: '2-digit', month: 'long', year: 'numeric' }

  return data.toLocaleDateString('pt-BR', options)
}

export function isAniversarioHoje(dataStr) {
  return diasAteAniversario(dataStr) === 0
}

export function isAniversarioNoMes(dataStr) {
  return mesDoAniversario(dataStr) === hoje.getMonth()
}

export function iniciaisNome(nome) {
  return nome
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((parte) => parte[0].toUpperCase())
    .join('')
}

const PALETA_AVATAR = [
  ['#7c3aed', '#a855f7'],
  ['#0891b2', '#06b6d4'],
  ['#be185d', '#ec4899'],
  ['#b45309', '#f59e0b'],
  ['#047857', '#10b981'],
  ['#1d4ed8', '#3b82f6'],
]

export function corAvatar(nome) {
  return PALETA_AVATAR[nome.charCodeAt(0) % PALETA_AVATAR.length]
}
