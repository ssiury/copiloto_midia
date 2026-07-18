# Prompt — Claude Code: Feature Aniversariantes

## Contexto do projeto

Estou construindo o **MídiaIgreja**, um sistema de gestão de conteúdo para o Ministério de Mídia de uma igreja. O sistema já possui um dashboard funcional em `dashboard-v2.html` (arquivo único, HTML + CSS + JS inline, tema dark, sidebar com 4 itens: Dashboard, Aniversariantes, Legendas, Configurações).

Preciso que você implemente a **feature completa de Aniversariantes**, cobrindo duas partes:

---

## Parte 1 — Página de Aniversariantes (CRUD de membros)

A navegação da sidebar já existe. Quando o usuário clicar em **"Aniversariantes"** na sidebar, o conteúdo principal deve trocar para a página de aniversariantes (SPA — sem reload de página).

### O que a página deve ter:

**Cabeçalho da página:**
- Título "Aniversariantes"
- Botão "+ Cadastrar Membro" no canto direito

**Lista de membros cadastrados:**
- Cards ou linhas em tabela com: foto do membro (avatar com iniciais se não tiver foto), nome, data de nascimento (exibir só dia/mês se `ignorar_ano = true`, senão exibir dia/mês/ano), próximo aniversário (ex: "em 5 dias" ou "hoje 🎉"), status ativo/inativo
- Ações por membro: Editar, Ativar/Desativar, Excluir (com confirmação)
- Estado vazio: mensagem "Nenhum membro cadastrado ainda" com botão de cadastro

**Modal de cadastro/edição com os campos:**
- `nome` — texto obrigatório
- `foto` — upload de imagem (JPG/PNG/WEBP). Armazenar o binário em `BYTEA` no banco. Exibir preview antes de salvar
- `data_nascimento` — date picker
- `ignorar_ano` — checkbox "Não sei o ano / exibir só dia e mês"
- `whatsapp` — texto com máscara `+55 (00) 00000-0000`
- `observacoes` — textarea opcional
- `ativo` — toggle (padrão: true)

---

## Parte 2 — Integração com o Dashboard

O dashboard já exibe um banner de aniversariante hardcoded. Preciso que ele seja **dinâmico**:

### Regra de exibição no dashboard:

1. Ao carregar o dashboard, verificar na lista de membros se algum tem aniversário **hoje** (comparar dia e mês com a data atual)
2. Se houver, exibir o **banner amarelo** de alerta com: nome do membro, foto (ou avatar com iniciais), texto "faz aniversário hoje!" e botão "Ver Arte" (pode ser placeholder por enquanto)
3. Se houver **mais de um** aniversariante no dia, empilhar os banners ou exibir "X aniversariantes hoje" com lista expandível
4. Se não houver nenhum, **ocultar o banner completamente**

### Card de resumo no dashboard:

O card "Aniversariantes no mês" (já existe hardcoded com valor `4`) deve ser atualizado dinamicamente com a contagem real de membros ativos que têm aniversário no mês atual.

---

## Modelagem de dados

Como o sistema é atualmente um protótipo frontend (HTML/JS puro), **simule o banco de dados com `localStorage`** seguindo exatamente esta estrutura:

```js
// Tabela: membros
{
  id: "uuid-v4",
  nome: "Maria Santos",
  foto: null,              // base64 string da imagem (null se não tiver)
  foto_tipo: "image/jpeg", // MIME type
  foto_nome: "maria.jpg",  // nome original
  data_nascimento: "1990-07-18", // ISO date (use "1900-07-18" se ignorar_ano = true)
  ignorar_ano: false,
  whatsapp: "+5562999990000",
  ativo: true,
  observacoes: "",
  criado_em: "2026-07-18T10:00:00Z",
  atualizado_em: "2026-07-18T10:00:00Z"
}
```

> **Importante:** ao salvar a foto, converta o arquivo para base64 no frontend e armazene como string no localStorage (simulando o BYTEA do banco). Ao exibir, use `data:${foto_tipo};base64,${foto}` como `src` da `<img>`.

---

## Restrições de implementação

- **Tudo em um único arquivo HTML** — sem dependências externas além da fonte Google Fonts já importada
- Manter o **tema dark** atual (`--bg: #0e0e12`, `--accent: #8b5cf6`, etc.)
- Manter o **mesmo layout**: sidebar fixa à esquerda (220px), conteúdo à direita com padding `40px 36px`
- Navegação por SPA: ao clicar nos itens da sidebar, trocar apenas o `innerHTML` da área de conteúdo principal — não recarregar a página
- Modal deve ter backdrop blur e fechar ao clicar fora ou no botão ✕
- Validação básica nos campos obrigatórios antes de salvar
- Feedback visual ao salvar/editar/excluir (toast notification simples no canto inferior direito)

---

## Arquivo base

O arquivo a ser modificado é `dashboard-v2.html`. Ele já contém:
- Estrutura HTML completa com sidebar e área `<main>`
- Todas as variáveis CSS (`:root { ... }`)
- Função `setActive(el)` para controle do item ativo na sidebar
- Função `updateGreeting()` para saudação dinâmica
- 4 itens de navegação já criados na sidebar

Adicione ao `onclick` de cada `nav-item` a chamada para a função de roteamento que você vai criar, por exemplo: `navigate('dashboard')`, `navigate('aniversariantes')`, etc.

---

## Comportamento esperado (fluxo completo)

```
Usuário abre o sistema
  → Dashboard carrega
  → JS verifica localStorage por aniversariantes do dia
  → Se houver: exibe banner amarelo com nome e foto
  → Card "Aniversariantes no mês" mostra contagem real

Usuário clica em "Aniversariantes" na sidebar
  → Conteúdo troca para o CRUD de membros
  → Lista todos os membros cadastrados
  → Se lista vazia: mostra estado vazio com botão de cadastro

Usuário clica em "+ Cadastrar Membro"
  → Modal abre
  → Preenche nome, faz upload de foto, seleciona data
  → Clica em "Salvar"
  → Modal fecha, toast aparece "Membro cadastrado com sucesso"
  → Lista atualiza com o novo membro

Usuário volta ao Dashboard
  → Se o membro cadastrado faz aniversário hoje: banner aparece
```
