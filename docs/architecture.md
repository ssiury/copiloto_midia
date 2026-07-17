# Arquitetura do projeto

> Este documento descreve como o código está organizado **hoje** (fim da
> Entrega 1 — base de autenticação). Ele reflete o estado real do repositório,
> não o estado ideal descrito em `docs/guia_projeto.md` — onde os dois
> divergem, isso está marcado explicitamente.
>
> **Ver também**: `docs/convencoes-arquitetura.md` — o acordo de como
> código **novo** deve ser organizado (camada `Application`, `Services` só
> para helpers, arquivo de strings no frontend etc.).

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
A ideia (ver `docs/guia_projeto.md`) é que cada módulo possa, no futuro, virar
um serviço HTTP separado sem precisar reescrever a regra de negócio — hoje
eles só se comunicam por chamada de método PHP direta (nada de HTTP interno
ainda).

Módulos existentes:

| Módulo | Status | O que faz |
|---|---|---|
| `Modules/Auth` | ✅ Implementado | Registro, login, logout, usuário autenticado |
| `Modules/Subscription` | ⚠️ Só scaffold | Planos/assinaturas — ainda não implementado (Entrega 2) |
| `Modules/App` | ⚠️ Só scaffold | Reservado para a regra de negócio principal (posts/agenda/etc.) — ainda não implementado |

`app/` (fora de `Modules/`) é só o esqueleto padrão do Laravel: o `User`
model, o `Controller` base e o `AppServiceProvider`. Não tem regra de negócio
aqui — é só infraestrutura compartilhada entre módulos.

### As camadas dentro de um módulo (exemplo: `Auth`)

**O padrão Controller → Application → Repository → Model existe e está
100% implementado no módulo `Auth`** — ele é a referência estrutural para
qualquer módulo novo (ver `docs/convencoes-arquitetura.md` para o detalhe
de cada camada e as regras). `Subscription` e `App` ainda são só o scaffold
que o comando `php artisan module:make` gera (controller vazio com métodos
`index/create/store/show/edit/update/destroy`, sem `Application` nem
repository).

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
Application?** Está documentado assim de propósito em `docs/guia_projeto.md`
e detalhado em `docs/convencoes-arquitetura.md`: a `AuthApplication` não
conhece a implementação concreta (`EloquentUserRepository`), só o contrato
(`UserRepositoryInterface`). O bind entre os dois acontece em
`AuthServiceProvider::register()`:

```php
$this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
```

Isso permite trocar a implementação (ex.: cache, outro banco, chamada HTTP
para um serviço extraído) sem tocar na `AuthApplication` nem no
`AuthController`.

### Application vs. Services — por que dois nomes parecidos

`Application/` é a regra de negócio/caso de uso (o `AuthApplication` acima).
`Services/` (ainda não existe no `Auth`, porque ele não precisou de nenhum
helper puro até agora) é reservado para funções reutilizáveis sem regra de
negócio — formatador, gerador de slug, algoritmo de distribuição. Os dois
nomes existem de propósito, para diferenciar "decide o que fazer" de "sabe
fazer um cálculo/transformação". Ver `docs/convencoes-arquitetura.md` para
o detalhe completo (inclusive o papel do DTO na fronteira
`Controller ↔ Application`).

### Resposta e erro padronizados

Definido globalmente em `bootstrap/app.php` (`withExceptions`), não em um
`Handler.php` separado (mudança de local entre Laravel 10 e 11/12, mas mesmo
efeito). Toda exceção em rota `/api/*` vira:

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
- Seeders: `database/seeders/DatabaseSeeder.php` chama `OwnerSeeder`, que cria
  o usuário owner de desenvolvimento via `updateOrCreate` (idempotente).

### Testes

Pest, em `tests/Feature/Auth/` — cobrem registro, login, logout, rota
protegida e rate limit. Não há testes de `Subscription`/`App` porque essas
áreas ainda não têm código de negócio.

---

## Frontend (`frontend/`)

SPA Vue 3 (`<script setup>`), sem SSR. Estrutura:

```
frontend/src/
├── views/          # uma "página" por rota (HomeView, LoginView, RegisterView, DashboardView)
├── stores/          # Pinia — estado global (auth.js, theme.js)
├── services/http.js # instância única do Axios (baseURL + interceptors)
├── strings/pt-BR.js # todo texto de interface centralizado (nenhuma view tem string literal)
├── router/index.js  # rotas + guard de autenticação
└── components/       # componentes reutilizáveis (ex.: ThemeToggle)
```

Aqui **não existe camada de Repository/Service separada** como no backend —
e isso é intencional, não uma peça faltando. O fluxo é:

```
View (.vue)  →  Pinia store (actions)  →  http.js (Axios)  →  API Laravel
```

A `useAuthStore` (`stores/auth.js`) concentra as chamadas HTTP relacionadas a
autenticação (`login`, `register`, `logout`, `fetchMe`) e o estado (`token`,
`user`). As views chamam a store, nunca o Axios direto. Isso já cumpre, numa
escala pequena, o mesmo papel que um "repository de frontend" cumpriria — para
o tamanho atual do projeto, uma camada extra só de indireção não teria
benefício (regra de "não adicionar abstração além do necessário"). Se no
futuro as chamadas de API crescerem muito por store, o próximo passo natural
é extrair funções puras em `services/` (ex.: `services/authApi.js`) chamadas
pela store — mas isso ainda não foi necessário.

Roteamento (`router/index.js`) tem um guard global
(`router.beforeEach`) que redireciona para `/login` quando a rota tem
`meta: { requiresAuth: true }` e o usuário não está autenticado
(`authStore.isAuthenticated`, derivado da existência do token).

---

## Infra (Docker)

`docker-compose.yml` na raiz sobe 5 serviços:

| Serviço | Papel |
|---|---|
| `app` | PHP-FPM rodando o Laravel (`backend/`) |
| `nginx` | serve o Laravel, roteia para `app` |
| `postgres` | banco de dados |
| `redis` | cache, rate limiting |
| `frontend` | `npm run dev` (Vite) com hot reload |

---

## Pontas soltas conhecidas (não é para copiar como padrão)

- **`Modules/*/routes/web.php` e `resources/views/*.blade.php`** em todos os
  3 módulos são sobra do scaffold automático do `php artisan module:make`
  (`nwidart/laravel-modules`), não uma segunda forma de servir a aplicação.
  Usam `Route::resource(...)` com middleware de sessão web (`auth`,
  `verified`), o que não bate com o resto do projeto (Sanctum + API). Como o
  front real é o Vue, esses arquivos deveriam ser removidos (ou os módulos
  configurados para não gerar rotas/views web nas próximas vezes que rodar
  `module:make`).
- **`Subscription` e `App`** têm só o controller-scaffold padrão (sem
  `Application`/`Repository`) porque ainda não chegaram nas Entregas 2/3 do
  `docs/guia_projeto.md`. Quando isso acontecer, seguir a mesma estrutura do
  `Auth` (Controller fino → `Application` com a regra e DTOs próprios →
  `Repository`/Interface → Model) — ver `docs/convencoes-arquitetura.md`,
  não inventar um padrão novo.
