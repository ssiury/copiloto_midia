# Estado atual do código

> Este documento descreve como o código está organizado **hoje**, depois do
> merge que trouxe a Entrega 2 (`Subscription`) do `origin/master` para esta
> branch. Ele reflete o estado real do repositório, não o estado ideal
> descrito em `docs/guia_projeto.md` — onde os dois divergem, isso está
> marcado explicitamente.
>
> **Ver também**: `docs/architecture.md` — plano de extração para
> microserviços (Entrega 3); `docs/convencoes-arquitetura.md` — o acordo de
> como código **novo** deve ser organizado (camada `Application`, `Services`
> só para helpers, DTOs, arquivo de strings no frontend etc.).

## Visão geral

```
copiloto_midia/
├── backend/     # Laravel 12 — API REST (monólito modular)
├── frontend/    # Vue 3 — SPA (consome a API via Axios)
└── docker-compose.yml
```

O backend **não renderiza nenhuma tela**. Ele existe só para expor
`/api/v1/...` em JSON. Todo o front visual é o `frontend/` em Vue — os dois
se comunicam só por HTTP, nunca compartilham código.

---

## Backend (`backend/`)

### Monólito modular com `nwidart/laravel-modules`

Em vez de um único `app/` gigante, o backend é dividido em **módulos**
independentes dentro de `Modules/`, cada um com sua própria pasta
`Http/Controllers`, `Services`, `Repositories`, `routes/`, `database/` etc.

Módulos existentes:

| Módulo | Status | O que faz |
|---|---|---|
| `Modules/Auth` | ✅ Implementado | Registro, login, logout, usuário autenticado. Segue a convenção `Application`/DTO (ver abaixo). |
| `Modules/Subscription` | ✅ Implementado | Planos/assinaturas (Entrega 2). Usa o padrão antigo (`Services/PlanService.php`, sem `Application`/DTO) — ver "Pontas soltas". |
| `Modules/App` | ⚠️ Só scaffold | Reservado para a regra de negócio principal (posts/agenda/etc.) — ainda não implementado |

`app/` (fora de `Modules/`) é só o esqueleto padrão do Laravel: o `User`
model, o `Controller` base e o `AppServiceProvider`. Não tem regra de negócio
aqui — é só infraestrutura compartilhada entre módulos.

### As camadas dentro de um módulo (exemplo: `Auth`)

**O padrão Controller → Application → Repository → Model existe e está
100% implementado no módulo `Auth`** — ele é a referência estrutural para
qualquer módulo novo (ver `docs/convencoes-arquitetura.md` para o detalhe
de cada camada e as regras). `Subscription` foi implementado com um padrão
diferente (`Controller → Service` direto, sem `Application` nem DTOs) antes
dessa convenção existir; `App` ainda é só o scaffold que o comando
`php artisan module:make` gera.

```
Modules/Auth/
├── app/
│   ├── Http/
│   │   ├── Controllers/AuthController.php     ← fino, só orquestra
│   │   ├── Requests/LoginRequest.php           ← valida o payload de entrada
│   │   ├── Requests/RegisterRequest.php
│   │   └── Resources/AuthResource.php          ← formata a resposta JSON
│   │       Resources/UserResource.php             (nunca devolve o Model cru)
│   ├── Application/
│   │   ├── AuthApplication.php                 ← regra de negócio
│   │   └── Data/
│   │       ├── RegisterData.php                ← DTO de entrada
│   │       ├── LoginData.php                   ← DTO de entrada
│   │       └── LoginResult.php                 ← DTO de saída
│   ├── Repositories/
│   │   ├── UserRepositoryInterface.php         ← contrato de acesso a dado
│   │   └── EloquentUserRepository.php          ← implementação via Eloquent
│   ├── Events/UserRegistered.php               ← disparado após registro
│   ├── Exceptions/InvalidCredentialsException.php
│   │   Exceptions/TooManyLoginAttemptsException.php
│   └── Providers/AuthServiceProvider.php       ← faz o bind da interface
├── routes/
│   ├── api.php   ← rotas reais (/api/v1/auth/...)
│   └── web.php   ← ⚠️ lixo de scaffold, ver seção "Pontas soltas"
└── resources/views/ ← ⚠️ idem, não é usado (front é Vue)
```

Fluxo de uma requisição (`POST /api/v1/auth/login`):

```
routes/api.php
  → AuthController::login()
    → valida via LoginRequest
    → monta o DTO LoginData e chama AuthApplication::login()
      → UserRepositoryInterface::findByEmail()  (interface, não a classe concreta)
        → EloquentUserRepository (implementação real, injetada pelo Laravel)
          → App\Models\User (Eloquent)
      → checa senha, rate limit, gera token Sanctum
      → devolve o DTO LoginResult
    → devolve o resultado como AuthResource (JSON)
```

**Por que interface + repository em vez de chamar `User::` direto na
Application?** A `AuthApplication` não conhece a implementação concreta
(`EloquentUserRepository`), só o contrato (`UserRepositoryInterface`). O
bind entre os dois acontece em `AuthServiceProvider::register()`:

```php
$this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
```

Isso permite trocar a implementação (ex.: cache, outro banco, chamada HTTP
para um serviço extraído) sem tocar na `AuthApplication` nem no
`AuthController`. Nota: `Auth` **não** tem uma `AuthServiceInterface`
própria (diferente do padrão de "toda dependência cross-módulo via
interface" que `docs/architecture.md` descreve para a Entrega 3) — porque
hoje nenhum outro módulo depende dele por classe concreta. Se isso mudar,
criar a interface nesse momento.

### Application vs. Services — por que dois nomes parecidos

`Application/` é a regra de negócio/caso de uso (o `AuthApplication`).
`Services/` é reservado para funções reutilizáveis sem regra de negócio —
formatador, gerador de slug, algoritmo de distribuição. `Subscription` usa
`Services/PlanService.php` com o sentido antigo (regra de negócio, não
helper) — é dívida a resolver quando o módulo for revisado, não um segundo
padrão válido. Ver `docs/convencoes-arquitetura.md` para o detalhe completo.

### Resposta e erro padronizados

Definido globalmente em `bootstrap/app.php` (`withExceptions`). Toda
exceção em rota `/api/*` vira:

```json
{ "error": { "code": "...", "message": "...", "details": {} } }
```

e toda resposta de sucesso segue `{ "data": {...}, "meta": {} }`
(ver `AuthResource`, `UserResource`, `routes/api.php`).

### Autenticação

Laravel Sanctum, modo *token* (não cookie/SPA-session): login devolve um
`plainTextToken`, o frontend guarda em `localStorage` e manda em
`Authorization: Bearer <token>` (ver `frontend/src/services/http.js`).
Rate limit de login: 5 tentativas/min por IP+email, via
`Illuminate\Cache\RateLimiter` (backend Redis) — implementado em
`AuthApplication::login()`.

### Banco de dados

- PostgreSQL 16, UUID como chave primária em `users` (`HasUuids` no Model).
- Tabela `users` tem `user_type` (`free`/`pro`/`owner`, default `free`).
- Seeders: `database/seeders/DatabaseSeeder.php` chama `OwnerSeeder`
  (`updateOrCreate`, idempotente) — criado antes de existir o comando
  oficial. **Agora existe `php artisan make:owner {email} {senha}`**
  (`Modules/Subscription/app/Console/Commands/MakeOwnerCommand.php`, do
  jeito que `docs/guia_projeto.md` sempre pediu, já atribuindo plano via
  `SubscriptionServiceInterface`) — os dois fazem a mesma coisa hoje; o
  `OwnerSeeder` deveria ser removido a favor do comando oficial numa
  limpeza futura (não removido agora pra não misturar com esse merge).

### Testes

Pest, em `tests/Feature/Auth/` (registro, login, logout, rota protegida,
rate limit) e `tests/Feature/Subscription/` (assinatura Free automática,
`make:owner`, `PlanService`) — 16 testes, todos passando. Não há testes de
`App` porque o módulo ainda não tem código de negócio.

---

## Frontend (`frontend/`)

SPA Vue 3 (`<script setup>`), sem SSR. Estrutura:

```
frontend/src/
├── views/          # uma "página" por rota (HomeView, LoginView, RegisterView, DashboardView)
├── layouts/         # DashboardLayout — sidebar fixa + <RouterView/> pro conteúdo
├── components/       # componentes reutilizáveis (Sidebar, ThemeToggle)
├── stores/          # Pinia — estado global (auth.js, theme.js, subscription.js)
├── services/http.js # instância única do Axios (baseURL + interceptors)
├── strings/pt-BR.js # todo texto de interface centralizado (nenhuma view tem string literal)
└── router/index.js  # rotas + guard de autenticação
```

Aqui **não existe camada de Repository/Service separada** como no backend —
e isso é intencional, não uma peça faltando. O fluxo é:

```
View (.vue)  →  Pinia store (actions)  →  http.js (Axios)  →  API Laravel
```

`stores/subscription.js` (`useSubscriptionStore`) já existe (`fetchSubscription`
→ `GET /v1/subscription/me`), mas **não está conectado a nenhuma tela ainda**
— o `DashboardView.vue` atual usa dado mockado (posts/agenda/aniversariante,
marcado com `// TODO`) e não consome a store de assinatura. Integrar os dois
é trabalho futuro, não feito neste merge de propósito (evitar misturar reconciliação
de conflito com feature nova).

Roteamento (`router/index.js`): `/dashboard` é uma rota pai
(`DashboardLayout`) com a tela real como filha de path vazio — telas
autenticadas futuras entram como rotas-irmãs no mesmo `children`, sem
duplicar a sidebar.

---

## Infra (Docker)

`docker-compose.yml` na raiz sobe 5 serviços: `app` (PHP-FPM), `nginx`,
`postgres`, `redis`, `frontend` (Vite dev server). O serviço `app` lê
configuração só de `backend/.env` (montado via volume) — de propósito sem
`environment:` duplicando `DB_*`/`REDIS_*` no `docker-compose.yml`, porque
isso já causou um bug sério (ver `phpunit.xml` e a nota de `force="true"`
nos comentários do arquivo): variável de ambiente real do container
vence o override de teste do PHPUnit sem `force="true"`, e os testes
chegaram a rodar contra o Postgres de dev e apagar dados reais via
`RefreshDatabase`.

---

## Pontas soltas conhecidas (não é para copiar como padrão)

- **`Modules/*/routes/web.php` e `resources/views/*.blade.php`** em todos os
  3 módulos são sobra do scaffold automático do `php artisan module:make`,
  não uma segunda forma de servir a aplicação (o front real é o Vue).
- **`Modules/Subscription` estava com classes faltando no `origin/master`
  (não introduzido por este merge) — corrigido aqui.** Vários arquivos
  importavam/usavam classes que não existiam em lugar nenhum do
  repositório: `Contracts\PlanServiceInterface`, `Contracts\SubscriptionServiceInterface`,
  `Services\SubscriptionService.php` (o arquivo inteiro não existia, só
  era importado/vinculado em `SubscriptionServiceProvider::register()`),
  `Http\Controllers\InternalSubscriptionController` (usado em
  `routes/api.php`) e `App\Http\Middleware\VerifyInternalApiKey` (usado em
  `bootstrap/app.php`). Recriados com base no que já era chamado pelos
  arquivos existentes (`PlanService.php`, `CheckPlanLimit`,
  `CreateFreeSubscription`, `MakeOwnerCommand`, `routes/api.php`) e no
  contrato descrito em `docs/architecture.md` — nenhuma regra de negócio
  nova foi inventada além do que já estava referenciado. Único ponto sem
  lastro nos testes: `registerPayment`/`PaymentData`/`Payment` (rotas
  internas de pagamento) — implementado como stub que só loga o evento,
  sem persistir em tabela própria (não existe migration de `payments`;
  criar quando um gateway real for integrado, ver `docs/guia_projeto.md`
  Entrega 3).
- **`Subscription` usa `Services/PlanService.php` com o sentido antigo**
  (regra de negócio, sem `Application`, sem DTO) — não é pra copiar esse
  padrão em módulo novo, seguir `Auth` (ver
  `docs/convencoes-arquitetura.md`).
- **`OwnerSeeder.php` duplica `php artisan make:owner`** (ver seção "Banco
  de dados") — mantido por enquanto, remover numa limpeza futura.
