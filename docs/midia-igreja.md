# MídiaIgreja — Documentação do Sistema

> Plataforma de gestão de conteúdo para o Ministério de Mídia da Igreja.  
> Centraliza criação, aprovação e publicação de conteúdo no Instagram e WhatsApp.

---

## Sumário

1. [Visão Geral](#visão-geral)
2. [Módulos do Sistema](#módulos-do-sistema)
3. [Fluxos Principais](#fluxos-principais)
4. [Modelagem de Dados](#modelagem-de-dados)
5. [Enums](#enums)
6. [Relacionamentos](#relacionamentos)
7. [Decisões Técnicas](#decisões-técnicas)
8. [Próximos Passos](#próximos-passos)

---

## Visão Geral

O **MídiaIgreja** foi projetado para reduzir o esforço manual da equipe de mídia, automatizando tarefas repetitivas como:

- Seleção e organização de fotos pós-culto com auxílio de IA
- Geração de legendas, textos de story e mensagens de WhatsApp
- Criação automática de artes para aniversariantes
- Agendamento e publicação nos canais da igreja (Instagram e WhatsApp)

### Princípio central

> A equipe cadastra uma vez. O sistema gera, a equipe revisa e aprova, o sistema publica.

---

## Módulos do Sistema

### 1. Dashboard

Tela inicial após o login. Exibe:

- Saudação dinâmica (Bom dia / Boa tarde / Boa noite) baseada no horário do sistema
- Resumo rápido: total de posts no mês, agendamentos pendentes e aniversariantes
- Alerta destacado caso haja aniversariante no dia
- Agenda do dia com eventos fixos e ocasionais e seus respectivos status

---

### 2. Aniversariantes

Gerencia o ciclo completo de parabenização de membros da igreja.

**Responsabilidades:**
- Cadastro de membros com nome, foto e data de nascimento
- Geração automática anual de ocorrências de aniversário
- Geração de arte, legenda para Instagram e mensagem de WhatsApp via IA
- Fluxo de aprovação antes da publicação
- Publicação em múltiplos canais com log por canal

---

### 3. Legendas

Gerencia templates de texto reutilizáveis para as publicações da igreja.

**Tipos de template:**
- Legenda para Instagram (`legenda_ig`)
- Texto de Story (`story`)
- Mensagem de WhatsApp (`whatsapp`)
- Arte de Post (`post`)
- Arte base para geração (`arte`)

Templates suportam variáveis dinâmicas: `{{nome}}`, `{{idade}}`, `{{dia}}`, `{{tema}}`.

---

### 4. Configurações

Área administrativa para:

- Gerenciar usuários e perfis de acesso
- Configurar canais (credenciais do Instagram e WhatsApp)
- Definir templates padrão por tipo de publicação
- Ajustar horários dos eventos recorrentes

---

## Fluxos Principais

### Fluxo Pós-Culto (Fotos + Publicação)

```
Equipe arrasta fotos
        ↓
IA processa o lote
  ├── Seleciona as 10 melhores
  ├── Remove duplicadas
  ├── Detecta fotos borradas
  └── Organiza por momento (louvor, palavra, oração)
        ↓
Equipe informa o tema da mensagem
        ↓
IA gera automaticamente
  ├── Legenda para Instagram
  ├── Texto do Story
  ├── Mensagem para WhatsApp
  ├── Hashtags
  └── CTA (chamada para ação)
        ↓
Equipe seleciona template de arte
        ↓
Sistema gera
  ├── Story (vertical)
  ├── Post quadrado
  └── Capa de Reel
        ↓
Equipe clica em "Publicar" (ou agenda)
        ↓
Sistema publica no Instagram e copia versão para WhatsApp
```

---

### Fluxo de Aniversariante

```
Cadastro do membro (nome + foto + data)
        ↓
Job diário verifica aniversariantes do dia
        ↓
Sistema cria ocorrencia_aniversario para o ano corrente
        ↓
IA gera
  ├── Arte personalizada (foto + nome + template da igreja)
  ├── Legenda para Instagram
  └── Mensagem para o grupo do WhatsApp
        ↓
Status: aguardando_aprovacao
        ↓
Equipe revisa e aprova no dashboard
        ↓
Sistema publica nos canais configurados
        ↓
Registro em publicacoes_aniversario por canal
```

---

### Fluxo de Evento Recorrente (ex: Célula)

```
Evento cadastrado com regra de recorrência
(ex: toda terça e quinta às 08:00)
        ↓
Job dispara no horário configurado
        ↓
Sistema envia mensagem ao grupo do WhatsApp
usando o template associado ao evento
        ↓
Log de envio registrado
```

---

## Modelagem de Dados

### `membros`

Cadastro único de cada pessoa da igreja.

| Campo           | Tipo           | Null | Descrição                                                    |
|-----------------|----------------|------|--------------------------------------------------------------|
| `id`            | UUID           | ✕    | Chave primária. `gen_random_uuid()`                          |
| `nome`          | VARCHAR(120)   | ✕    | Nome completo                                                |
| `foto`          | BYTEA          | ○    | Binário da imagem armazenado diretamente no banco            |
| `foto_tipo`     | VARCHAR(50)    | ○    | MIME type. Ex: `image/jpeg`, `image/png`, `image/webp`       |
| `foto_nome`     | VARCHAR(255)   | ○    | Nome original do arquivo. Ex: `maria-santos.jpg`             |
| `data_nascimento` | DATE         | ✕    | Se ano desconhecido, usar `1900-MM-DD` + flag `ignorar_ano`  |
| `ignorar_ano`   | BOOLEAN        | ✕    | `true` → exibe só dia/mês, não calcula idade                 |
| `whatsapp`      | VARCHAR(20)    | ○    | Número com DDI. Ex: `+5562999990000`                         |
| `ativo`         | BOOLEAN        | ✕    | Soft delete — desativa sem excluir histórico                 |
| `observacoes`   | TEXT           | ○    | Notas internas da equipe                                     |
| `criado_em`     | TIMESTAMPTZ    | ✕    | `DEFAULT NOW()`                                              |
| `atualizado_em` | TIMESTAMPTZ    | ✕    | Atualizado via trigger                                       |

---

### `templates_aniversario`

Modelos de conteúdo reutilizáveis pela equipe.

| Campo           | Tipo           | Null | Descrição                                                       |
|-----------------|----------------|------|-----------------------------------------------------------------|
| `id`            | UUID           | ✕    | `gen_random_uuid()`                                             |
| `nome`          | VARCHAR(80)    | ✕    | Ex: `Padrão Igreja 2025`                                        |
| `tipo`          | ENUM           | ✕    | `legenda_ig` `story` `post` `whatsapp` `arte`                   |
| `conteudo`      | TEXT           | ✕    | Texto com variáveis: `{{nome}}` `{{idade}}` `{{dia}}`           |
| `arte_base`     | BYTEA          | ○    | Binário do arquivo base da arte (PNG de referência para geração)|
| `arte_base_tipo`| VARCHAR(50)    | ○    | MIME type do arquivo base. Ex: `image/png`                      |
| `arte_base_nome`| VARCHAR(255)   | ○    | Nome original. Ex: `template-aniversario-2025.png`              |
| `padrao`        | BOOLEAN        | ✕    | Template selecionado automaticamente pela IA                    |
| `ativo`         | BOOLEAN        | ✕    | Oculta sem excluir                                              |
| `criado_em`     | TIMESTAMPTZ    | ✕    | `DEFAULT NOW()`                                                 |

---

### `ocorrencias_aniversario`

Uma linha por membro por ano. Centraliza o conteúdo gerado pela IA para aquele ciclo.

| Campo                 | Tipo           | Null | Descrição                                                            |
|-----------------------|----------------|------|----------------------------------------------------------------------|
| `id`                  | UUID           | ✕    | `gen_random_uuid()`                                                  |
| `membro_id`           | UUID (FK)      | ✕    | → `membros.id`. `ON DELETE CASCADE`                                  |
| `ano`                 | SMALLINT       | ✕    | Ex: `2026`. UNIQUE com `membro_id`                                   |
| `data_aniversario`    | DATE           | ✕    | Data exata neste ano. Ex: `2026-07-17`                               |
| `status`              | ENUM           | ✕    | `pendente` → `gerando` → `aguardando_aprovacao` → `aprovado` → `publicado` → `ignorado` |
| `template_id`         | UUID (FK)      | ○    | → `templates_aniversario.id`                                         |
| `arte`                | BYTEA          | ○    | Binário da arte gerada pela IA (PNG/JPEG final)                      |
| `arte_tipo`           | VARCHAR(50)    | ○    | MIME type. Ex: `image/png`                                           |
| `arte_nome`           | VARCHAR(255)   | ○    | Nome gerado. Ex: `aniversario-maria-2026.png`                        |
| `arte_tamanho_bytes`  | INTEGER        | ○    | Tamanho em bytes — para monitorar crescimento do banco               |
| `legenda_instagram`   | TEXT           | ○    | Texto gerado para post/story do Instagram                            |
| `mensagem_whatsapp`   | TEXT           | ○    | Versão formatada para o grupo do WhatsApp                            |
| `hashtags`            | TEXT[]         | ○    | Array de hashtags. Ex: `["#parabens", "#igrejafamilia"]`             |
| `gerado_em`           | TIMESTAMPTZ    | ○    | Quando a IA terminou de gerar                                        |
| `aprovado_em`         | TIMESTAMPTZ    | ○    | Quando a equipe aprovou                                              |
| `aprovado_por`        | UUID (FK)      | ○    | → `usuarios.id`                                                      |
| `criado_em`           | TIMESTAMPTZ    | ✕    | `DEFAULT NOW()`                                                      |

---

### `publicacoes_aniversario`

Uma linha por canal por ocorrência. Permite rastrear status independente por canal.

| Campo           | Tipo           | Null | Descrição                                                        |
|-----------------|----------------|------|------------------------------------------------------------------|
| `id`            | UUID           | ✕    | `gen_random_uuid()`                                              |
| `ocorrencia_id` | UUID (FK)      | ✕    | → `ocorrencias_aniversario.id`                                   |
| `canal`         | ENUM           | ✕    | `instagram_post` `instagram_story` `whatsapp_grupo` `whatsapp_direto` |
| `status`        | ENUM           | ✕    | `agendado` `publicando` `publicado` `falhou`                     |
| `agendado_para` | TIMESTAMPTZ    | ○    | Data/hora planejada                                              |
| `publicado_em`  | TIMESTAMPTZ    | ○    | Preenchido quando efetivamente publicado                         |
| `id_externo`    | TEXT           | ○    | ID retornado pela API do Instagram ou WhatsApp                   |
| `erro`          | TEXT           | ○    | Mensagem de erro quando `status = falhou`                        |
| `publicado_por` | UUID (FK)      | ○    | → `usuarios.id`. `NULL` quando automático                        |
| `criado_em`     | TIMESTAMPTZ    | ✕    | `DEFAULT NOW()`                                                  |

---

## Enums

```sql
-- Status do ciclo de aniversário
CREATE TYPE status_ocorrencia AS ENUM (
  'pendente',
  'gerando',
  'aguardando_aprovacao',
  'aprovado',
  'publicado',
  'ignorado'
);

-- Canais de publicação disponíveis
CREATE TYPE canal_publicacao AS ENUM (
  'instagram_post',
  'instagram_story',
  'whatsapp_grupo',
  'whatsapp_direto'
);

-- Status de cada publicação por canal
CREATE TYPE status_publicacao AS ENUM (
  'agendado',
  'publicando',
  'publicado',
  'falhou'
);

-- Tipos de template de conteúdo
CREATE TYPE tipo_template AS ENUM (
  'legenda_ig',
  'story',
  'post',
  'whatsapp',
  'arte'
);
```

---

## Relacionamentos

```
membros (1)
  └──< ocorrencias_aniversario (N)  [membro_id → membros.id]
         └──< publicacoes_aniversario (N)  [ocorrencia_id → ocorrencias_aniversario.id]

templates_aniversario (1)
  └──< ocorrencias_aniversario (N)  [template_id → templates_aniversario.id]

usuarios (1)
  ├──< ocorrencias_aniversario (N)  [aprovado_por → usuarios.id]
  └──< publicacoes_aniversario (N)  [publicado_por → usuarios.id]
```

---

## Decisões Técnicas

### Imagens em `BYTEA`

Fotos e artes são armazenadas como binário direto no banco. Cada campo binário vem acompanhado de:

- `_tipo` — MIME type (`image/jpeg`, `image/png`, `image/webp`)
- `_nome` — nome original do arquivo
- `_tamanho_bytes` — nas artes geradas, para monitorar crescimento

**Para servir ao frontend:**
- Endpoint dedicado (`GET /membros/:id/foto`) retorna o binário como stream com `Content-Type` do campo `_tipo`
- Evitar Base64 inline no JSON para não inflar o payload

> **Atenção:** nunca indexe colunas `BYTEA`. Use `arte_tamanho_bytes` para auditoria de crescimento do banco.

---

### Separação Membro × Ocorrência

O membro é cadastrado **uma única vez**. A cada ano, uma nova `ocorrencia_aniversario` é criada automaticamente pelo job diário. Isso garante:

- Histórico completo de aniversários anteriores
- Possibilidade de comparar artes e textos entre anos
- Nenhum dado anterior é sobrescrito

---

### Soft Delete

Membros utilizam `ativo = false` em vez de `DELETE`. Vantagens:

- Histórico de ocorrências e publicações permanece intacto
- `VACUUM` do PostgreSQL libera os `BYTEA` apenas quando o registro for de fato excluído
- Possibilidade de reativar membros sem perder dados

---

### Log por Canal

`publicacoes_aniversario` tem **uma linha por canal**. Isso permite que:

- O Instagram falhe enquanto o WhatsApp é enviado com sucesso
- Cada canal tenha seu próprio `id_externo` retornado pela API
- Retry granular: apenas o canal que falhou é tentado novamente

---

### Índices recomendados

```sql
-- Consulta do dia (job + dashboard)
CREATE INDEX idx_membros_nascimento ON membros (data_nascimento);

-- Job diário de geração de ocorrências
CREATE INDEX idx_ocorrencias_data_status ON ocorrencias_aniversario (data_aniversario, status);

-- Evita criar ocorrência duplicada no mesmo ano
CREATE UNIQUE INDEX uq_ocorrencias_membro_ano ON ocorrencias_aniversario (membro_id, ano);

-- Listagem de publicações por ocorrência
CREATE INDEX idx_publicacoes_ocorrencia ON publicacoes_aniversario (ocorrencia_id);
```

---

## Próximos Passos

- [ ] Definir modelagem do módulo **Eventos** (fixos e ocasionais)
- [ ] Definir modelagem do módulo **Legendas** (templates gerais)
- [ ] Definir modelagem do módulo **Usuários e Permissões**
- [ ] Criar SQL de criação das tabelas (`CREATE TABLE`)
- [ ] Prototipar tela de **Aniversariantes** no front
- [ ] Prototipar tela de **Legendas** no front
- [ ] Definir stack de backend (API REST / framework)
- [ ] Definir integração com API do Instagram (Graph API)
- [ ] Definir integração com WhatsApp (Evolution API / Baileys)
- [ ] Definir estratégia de geração de arte (canvas / sharp / API de IA)

---

*Última atualização: julho de 2026*
