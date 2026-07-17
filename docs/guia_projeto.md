# PROMPT — copiloto_midia: Base de SaaS Multi-tenant (Free/Pro/Owner) — Laravel + Vue

## Contexto e decisão de arquitetura (leia antes de começar)

Este projeto é a base de um SaaS. **Decisão de arquitetura**: começar como
**monólito modular** em Laravel (não como microserviços literais/repos
separados desde já). Use `nwidart/laravel-modules` para separar o código em
módulos (`Auth`, `Subscription`, `App`) com fronteiras claras (cada módulo com
suas próprias rotas, controllers, services, models, migrations). Isso permite
extrair qualquer módulo para um serviço/repo separado no futuro sem reescrever
lógica de negócio — só trocar a camada de transporte (HTTP interno) e mover a
pasta do módulo.

**Não crie 3 repositórios Laravel separados agora.** Isso é over-engineering
nesta fase e vai gerar overhead de infraestrutura sem benefício real. A
comunicação entre módulos deve acontecer via **interfaces/contratos internos
(Service classes injetadas)**, não via HTTP, até que haja motivo real para
separar fisicamente.

Se você (Claude Code) achar, durante a implementação, que alguma parte deste
prompt está ambígua ou contraditória, **pare e pergunte antes de assumir**.

---

## Stack (versões fixadas)

**Backend**
- PHP 8.3
- Laravel 11.x
- PostgreSQL 16
- Laravel Sanctum (autenticação SPA via token — não usar Passport/JWT puro,
  Sanctum é o padrão recomendado do Laravel para esse caso de uso)
- Pest para testes (não PHPUnit puro)
- `nwidart/laravel-modules` para modularização

**Frontend**
- Vue 3 (Composition API + `<script setup>`)
- Vue Router
- Pinia (state management — necessário para guardar usuário autenticado,
  plano atual, etc.)
- Bootstrap 5
- Axios (com interceptor central para token e tratamento de erro 401)

**Infra**
- Docker + Docker Compose
- Serviços no `docker-compose.yml`: `app` (PHP-FPM), `nginx`, `postgres`,
  `frontend` (Vite dev server), `redis` (para rate limiting e cache — usar
  desde já em vez de driver `array`, para já nascer correto)

---

## Padrões de resposta e erro da API (definir agora, usar em tudo)

Formato padrão de sucesso:
```json
{
  "data": { ... },
  "meta": { }
}
```

Formato padrão de erro:
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Mensagem segura para o usuário",
    "details": { "campo": ["mensagem"] }
  }
}
```

Implementar isso via um `Handler` global de exceções (`bootstrap/app.php` no
Laravel 11 ou `app/Exceptions/Handler.php`) que converte todas as exceções
(validação, autenticação, not found, erro genérico) para esse formato. Nunca
vazar stack trace ou mensagem de exceção crua em produção.

Toda API deve ser versionada via prefixo de rota: `/api/v1/...`.

---

## ENTREGA 0 — Setup Inicial do Projeto (scaffolding)

### Objetivo
Deixar o projeto rodando localmente do zero antes de escrever qualquer regra
de negócio. Nenhuma feature de autenticação/planos entra nesta entrega — só
estrutura, dependências e "hello world" funcionando ponta a ponta.

### Estrutura de pastas do repositório
```
copiloto_midia/
├── backend/                 # projeto Laravel
│   ├── Modules/              # onde os módulos (Auth, Subscription, App) vão viver
│   ├── app/
│   ├── docker/
│   │   ├── php/
│   │   │   └── Dockerfile
│   │   └── nginx/
│   │       └── default.conf
│   └── ...
├── frontend/                 # projeto Vue
│   ├── src/
│   │   ├── views/
│   │   ├── stores/            # Pinia
│   │   ├── router/
│   │   └── services/           # axios instance + interceptors
│   └── ...
├── docs/
│   └── architecture.md
├── docker-compose.yml
├── .env.example
└── README.md
```

### Passo a passo

**1. Repositório e Git**
- `git init` na raiz `copiloto_midia/`
- `.gitignore` cobrindo `vendor/`, `node_modules/`, `.env`, `storage/*.key`,
  builds do Vite

**2. Backend — Laravel**
- Criar projeto Laravel 11 dentro de `backend/`
- Instalar e publicar config do Sanctum
- Instalar `nwidart/laravel-modules` e rodar o setup inicial dele
- Criar os 3 módulos vazios desde já (apenas a estrutura, sem lógica):
  `php artisan module:make Auth Subscription App`
- Configurar conexão do `.env` para apontar para o container `postgres`
- Configurar `config/cache.php` e `config/session.php` para usar Redis
- Configurar CORS (`config/cors.php`) já apontando para a origem do frontend
  via variável de ambiente

**3. Frontend — Vue**
- Criar projeto Vue 3 com Vite dentro de `frontend/`
- Instalar Vue Router, Pinia, Axios, Bootstrap 5
- Criar instância central do Axios (`src/services/http.js`) já com
  `baseURL` vindo de variável de ambiente (`VITE_API_URL`) e interceptor
  vazio (será preenchido na Entrega 1 com o token)
- Estrutura mínima de rotas (Router) com uma página placeholder (`/`)

**4. Docker**
- `docker-compose.yml` na raiz com os serviços: `app` (PHP-FPM, build a
  partir de `backend/docker/php/Dockerfile`), `nginx` (servindo o Laravel),
  `postgres` (com volume nomeado para persistência), `redis`, `frontend`
  (rodando `npm run dev` do Vite, com hot reload exposto)
- `Dockerfile` do PHP com as extensões necessárias para Laravel + PostgreSQL
  (`pdo_pgsql`, etc.)
- Variáveis sensíveis (senha do banco, etc.) vindas de `.env` na raiz, lidas
  pelo `docker-compose.yml`

**5. Sanity check**
- Subir tudo com `docker-compose up` e confirmar:
    - Laravel responde em uma rota de health check (`GET /api/v1/health`
      retornando `{"data": {"status": "ok"}}`, já no formato de resposta padrão
      definido acima)
    - Vue carrega no navegador e consegue chamar essa rota de health check via
      Axios, provando que CORS e proxy estão corretos
    - `postgres` está acessível a partir do container `app`
    - `redis` está acessível a partir do container `app`

**6. README**
- `README.md` com instruções de como subir o ambiente (`docker-compose up`,
  variáveis de `.env` necessárias, comandos de migration)

### Definition of done (Entrega 0)
- [ ] `docker-compose up` sobe todos os 5 serviços sem erro
- [ ] Rota `/api/v1/health` responde no formato padrão de sucesso
- [ ] Frontend em `http://localhost:<porta>` consegue chamar essa rota sem
  erro de CORS
- [ ] Os 3 módulos (`Auth`, `Subscription`, `App`) existem via
  `laravel-modules`, mesmo vazios
- [ ] `README.md` permite que qualquer pessoa suba o projeto do zero seguindo
  só as instruções

---

## ENTREGA 1 — Estrutura Base e Autenticação

### Backend
Módulo `Auth` (via laravel-modules) com:
- Cadastro de usuário (`POST /api/v1/auth/register`)
- Login (`POST /api/v1/auth/login`) — gera token via Sanctum
- Logout (`POST /api/v1/auth/logout`) — revoga token atual
- Usuário autenticado (`GET /api/v1/auth/me`)
- Middleware `auth:sanctum` protegendo rotas privadas
- Rate limit no login: 5 tentativas / minuto por IP+email (usar
  `Illuminate\Cache\RateLimiter`, backend Redis)
- Verificação de e-mail: **não implementar agora**, mas deixar campo
  `email_verified_at` na tabela (padrão do Laravel) para não precisar de
  migration extra depois

### Frontend
- Tela de Login
- Tela de Cadastro
- Dashboard simples pós-login mostrando nome e email do usuário
- Guard de rota no Vue Router redirecionando não-autenticados para login
- Store Pinia (`useAuthStore`) guardando token e usuário

### Banco de dados — tabela `users`
```
id (uuid, chave primária)
name (string)
email (string, unique)
password (string, hash)
user_type (enum: free, pro, owner — default: free)
email_verified_at (timestamp, nullable)
created_at, updated_at
```
Usar UUID como chave primária (não `bigint` incremental) — padrão melhor para
sistemas que podem virar distribuídos no futuro, evita colisão de IDs entre
serviços.

### Segurança
- Hash de senha via `bcrypt` (padrão Laravel)
- `LoginRequest` e `RegisterRequest` (Form Requests) para validação
- CORS configurado em `config/cors.php` liberando apenas o domínio do
  frontend (via `.env`, não `*`)
- Variáveis sensíveis em `.env`, nunca commitadas (`.env.example` sim)
- Log de tentativas de login (sucesso e falha) em canal dedicado
  (`storage/logs/auth.log`)

### Engenharia de software
Estrutura dentro do módulo `Auth`:
- `AuthController` (fino, só orquestra)
- `AuthService` (regra de negócio: login, logout, registro)
- `UserRepository` (interface + implementação Eloquent)
- `LoginRequest`, `RegisterRequest`
- `AuthResource`, `UserResource` (API Resources — nunca retornar Model cru)
- `UserRegistered` event (será usado na Entrega 2 para criar assinatura Free
  automaticamente — criar o evento já, mesmo sem listener ainda)

### Testes (Pest)
- Registro cria usuário com `user_type = free`
- Login retorna token válido
- Login com credenciais erradas retorna 401 com formato de erro padrão
- Logout revoga token (chamada subsequente com token antigo falha)
- Rota protegida retorna 401 sem token, 200 com token
- Rate limit bloqueia após 5 tentativas

### Definition of done (Entrega 1)
- [ ] `docker-compose up` sobe tudo sem erro
- [ ] Cadastro → login → acessar `/me` → logout funciona ponta a ponta
- [ ] Todos os testes Pest listados acima passam
- [ ] Nenhuma senha ou token aparece em log ou resposta de erro

---

## ENTREGA 2 — Estrutura para Planos e Permissões

### Backend
Módulo `Subscription` com tabelas:

**`plans`**
```
id (uuid)
name (string)
slug (string, unique — free/pro/owner)
is_paid (boolean)
is_unlimited (boolean)
created_at, updated_at
```

**`plan_limits`**
```
id (uuid)
plan_id (uuid, fk)
resource (string — ex: "projects", "uploads")
limit (integer, nullable — null = sem limite)
created_at, updated_at
```

**`user_subscriptions`**
```
id (uuid)
user_id (uuid, fk)
plan_id (uuid, fk)
status (enum: active, canceled, expired, trial)
started_at (timestamp)
ends_at (timestamp, nullable)
created_at, updated_at
```

### Regras de negócio
- Listener no evento `UserRegistered` (criado na Entrega 1) cria automaticamente
  um `user_subscriptions` com plano Free e status `active`
- Owner é criado via `php artisan make:owner {email} {senha}` (Artisan Command
  customizado), nunca pelo formulário público de cadastro
- `PlanService` com métodos:
    - `canUseFeature(User $user, string $feature): bool`
    - `hasReachedLimit(User $user, string $resource): bool`
    - `isUnlimited(User $user): bool`
- Middleware `CheckPlanLimit` aplicável em rotas que consomem recurso limitado

### Frontend
- Seção no dashboard mostrando: tipo de usuário, plano atual, limites e uso
  atual de cada recurso, status da assinatura

### Testes (Pest)
- Novo usuário recebe assinatura Free ativa automaticamente
- Comando `make:owner` cria usuário com plano ilimitado e tipo `owner`
- `hasReachedLimit` retorna `true` quando free atinge o limite configurado
- `isUnlimited` retorna `true` para pro e owner

### Definition of done (Entrega 2)
- [ ] Cadastro de novo usuário já cria assinatura Free sem passo manual
- [ ] Dashboard mostra plano e limites reais vindos da API (não mockado)
- [ ] Testes de limite e permissão passam

---

## ENTREGA 3 — Preparação para Extração em Microserviços e Pagamentos

### Objetivo
Preparar o monólito modular para, no futuro, extrair `Subscription` (e depois
`App`) como serviço HTTP separado — sem reescrever regra de negócio.

### O que fazer
- Garantir que `AuthService`, `PlanService` etc. só sejam chamados via
  **interface**, nunca instanciados diretamente nos controllers de outros
  módulos — isso é o que permite trocar a implementação por uma chamada HTTP
  depois sem tocar em quem consome
- Documentar em `docs/architecture.md` o plano de extração: qual módulo sai
  primeiro, qual contrato de API interna cada um exporia
- Preparar (sem implementar cobrança) os métodos de `SubscriptionService`:
  `createSubscription`, `cancelSubscription`, `renewSubscription`,
  `changePlan`, `registerPayment` — implementados com lógica local por
  enquanto, mas com assinatura de método já pensada para um gateway futuro
  (Stripe, Mercado Pago, Asaas ou Pagar.me — não integrar nenhum agora)
- Adicionar suporte a API keys internas (header `X-Internal-Key`) em rotas
  que, no futuro, seriam chamadas serviço-a-serviço — mesmo que hoje sejam
  chamadas internamente no mesmo processo

### Testes (Pest)
- Suite completa das Entregas 1 e 2 continua passando
- `changePlan` atualiza `user_subscriptions` corretamente
- Middleware de API key interna bloqueia requisição sem header válido

### Definition of done (Entrega 3)
- [ ] `docs/architecture.md` existe e descreve o plano de extração
- [ ] Nenhum módulo importa classe concreta de outro módulo diretamente
  (apenas interfaces)
- [ ] Suite de testes completa (Entregas 1–3) passa em CI

---

## Resultado esperado ao final

- Ambiente completo subindo com um único `docker-compose up`
- Login e cadastro seguros com Sanctum
- Usuários com `user_type` free/pro/owner, plano Free atribuído automaticamente
- Estrutura de planos e limites funcional e testada
- Monólito modular pronto para extração futura em microserviços reais
- Estrutura clara para plugar gateway de pagamento depois
- Testes automatizados cobrindo os fluxos críticos
- Docker Compose funcional subindo todo o ambiente com um comando