# Convenções de arquitetura — acordos para devs e agentes de IA

> **Propósito**: este documento é um acordo vivo sobre como código **novo**
> deve ser organizado neste projeto — backend (`Modules/<Nome>/app/`) e
> frontend (`frontend/src/`). Ele existe para que qualquer pessoa (ou
> agente de IA) que for escrever uma feature saiba, sem precisar perguntar,
> em qual arquivo cada tipo de código deve morar.
>
> **Escopo**: aplica-se a código novo e já foi aplicado retroativamente ao
> módulo `Modules/Auth`, que hoje segue 100% este documento — é a
> **implementação de referência**: use os arquivos dele como modelo ao criar
> `Subscription`/`App`. Ver `docs/architecture.md` para o estado atual real
> do código (o "as is"); este documento é o "acordo daqui pra frente".

---

## Parte 1 — Fluxo ponta a ponta

```
Vue View → Pinia store → Axios → [HTTP] → Request → Controller
   → (DTO) → Application → Repository (interface) → Model → DB
                 ↘ Services (helpers, quando precisar)
   ← (DTO) ← Application ← Resource ← JsonResponse ← Controller
```

Cada seta só pode andar num sentido: uma camada só conhece a camada
imediatamente abaixo dela (via interface, quando existir). `Controller`
nunca fala com `Repository` direto; `Application` nunca fala com `Model`/DB
direto. O dado que atravessa `Controller ↔ Application` é sempre um **DTO**
(ver seção própria abaixo) — nunca um `JsonResponse`/`Request` num sentido,
nem um array solto no outro.

---

## Parte 2 — Backend (`Modules/<Nome>/app/`)

### Request — `Http/Requests/`

Valida a entrada da requisição. `rules()`, `authorize()`, e nada além disso
— nenhuma regra de negócio aqui (ex.: "só pode se `user_type` for X" é
`Application`, não `Request`).

Referência: `Modules/Auth/app/Http/Requests/LoginRequest.php`.

### Controller — `Http/Controllers/`

Só orquestra: recebe o `Request` já validado, chama a `Application`, devolve
um `Resource`. Fino de propósito — se um controller tem um `if` decidindo
regra de negócio, ou acessa `Repository`/`Model` diretamente, isso é bug de
camada, não estilo.

Referência: `Modules/Auth/app/Http/Controllers/AuthController.php`.

### Application — `Application/`

A regra de negócio / caso de uso do módulo. Pode depender de:
- `Repository` — **só a interface**, nunca a implementação concreta
  (`Eloquent...Repository`).
- `Services` — helpers puros, quando precisar.
- `Events` / `Exceptions` do próprio módulo.

**Não pode**:
- Importar `Illuminate\Database\Eloquent\Model` (ou qualquer Model
  concreto).
- Chamar `DB::`, `Model::query()`, `Model::where(...)` etc. Se precisa
  buscar ou salvar algo, é sempre via `Repository`.

Nomeação: uma classe por módulo/domínio, sufixo `Application`
(`AuthApplication`, `SubscriptionApplication`) — **não** `...Service`, para
não colidir com o sentido de `Services/` neste projeto (ver abaixo).

Referência: `Modules/Auth/app/Application/AuthApplication.php`.

Se o caso de uso crescer e acumular responsabilidades não relacionadas,
dividir em classes invocáveis por caso de uso, ainda dentro de
`Application/` (ex.: `Application/RegisterUser.php`,
`Application/AuthenticateUser.php`). Isso é um critério de bom senso, não
uma regra rígida — comece com uma classe por módulo e só divida quando ela
realmente ficar difícil de navegar.

### DTO — `Application/Data/`

Como o dado atravessa a fronteira `Controller ↔ Application`. **Nunca**
`JsonResponse` (é um conceito HTTP — se a `Application` recebesse ou
devolvesse isso, ela passaria a conhecer request/response, quebrando a regra
de que `Application` não sabe que existe HTTP por trás). E, de preferência,
nunca um array solto (`['user' => ..., 'token' => ...]`) — o shape só existe
em PHPDoc, sem checagem em tempo de compilação.

Um DTO aqui é uma classe simples e imutável (`readonly`), com propriedades
tipadas, sem comportamento:

```php
// Modules/Auth/app/Application/Data/LoginData.php  (entrada)
final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $throttleKey,
    ) {
    }
}
```

```php
// Modules/Auth/app/Application/Data/LoginResult.php  (saída)
final readonly class LoginResult
{
    public function __construct(
        public User $user,
        public string $token,
    ) {
    }
}
```

Uso: o `Controller` monta o DTO de entrada a partir do `Request::validated()`
e passa pra `Application`; a `Application` devolve um DTO de saída, que o
`Controller` passa direto pro `Resource`:

```php
// Controller
public function login(LoginRequest $request): JsonResponse
{
    $result = $this->authApplication->login(new LoginData(
        email: $request->string('email')->toString(),
        password: $request->string('password')->toString(),
        throttleKey: $request->throttleKey(),
    ));

    return (new AuthResource($result))->response();
}

// Application
public function login(LoginData $data): LoginResult
{
    // ...regra de negócio...
    return new LoginResult(user: $user, token: $token);
}
```

Regras práticas:
- DTO de saída pode carregar um `Model` dentro (como `User` acima) — o que
  ele não pode carregar é lógica de acesso a banco. Devolver o Model em si
  não é o problema; o problema seria a `Application` fazer `Model::query()`.
- Não force DTO para um valor único e sem ambiguidade (ex.: um método que só
  recebe um `string $id`) — isso é ruído. DTO compensa quando 2+ valores
  relacionados viajam juntos.
- `JsonResponse` só nasce em um lugar: no `Controller` (ou dentro do
  `Resource->response()`, chamado pelo Controller). Nunca antes disso.
- Fronteira `Application ↔ Repository` não precisa de DTO — pode passar
  parâmetros simples (string, array) ou reaproveitar o mesmo DTO de entrada,
  o que for mais direto; o `Repository` sempre devolve `Model`/`?Model`.

### Services — `Services/`

**Não é regra de negócio.** São funções puras e reutilizáveis: formatador de
texto, gerador de slug, algoritmo de distribuição/agendamento, cálculo de
data etc. Sem I/O de banco, sem decidir permissão ou fluxo de usuário.

Teste rápido pra saber se algo é `Services` ou `Application`: um `Services`
não sabe "de quem" é o dado que está processando nem decide se algo é
permitido — só transforma o que recebe e devolve um resultado.

Onde mora:
- Específico de um módulo → `Modules/<Nome>/app/Services/`.
- Genérico, usado por mais de um módulo → `app/Services/` (raiz — hoje esse
  diretório ainda não existe; será criado na primeira vez que surgir um
  helper realmente compartilhado).

### Repository — `Repositories/`

Única camada que fala com Eloquent/DB diretamente. Sempre interface +
implementação:

```
Repositories/
├── XRepositoryInterface.php
└── EloquentXRepository.php
```

O bind acontece no `<Módulo>ServiceProvider::register()`
(`$this->app->bind(XRepositoryInterface::class, EloquentXRepository::class)`),
igual já acontece em `AuthServiceProvider`. Um repository por
agregado/entidade — não um repository genérico pra tudo. Métodos nomeados
pela intenção de negócio (`findByEmail`), nunca expor o query builder pra
fora da classe.

Referência: `Modules/Auth/app/Repositories/UserRepositoryInterface.php` e
`EloquentUserRepository.php`.

### Model

Eloquent puro: relacionamentos, casts, scopes simples de leitura. Sem regra
de negócio pesada (isso é `Application`). Fica em `app/Models` se for
compartilhado entre módulos (caso do `User` hoje) ou em
`Modules/<Nome>/app/Models` se pertence só a um módulo.

### Resource — `Http/Resources/`

Reforça convenção já existente no projeto: nunca devolver um Model cru numa
resposta de API — sempre via `JsonResource`.

### Mensagens/strings no backend

Para código novo, mensagens de erro/log voltadas ao usuário devem vir dos
arquivos de idioma do Laravel (`lang/<locale>/...` + helper `__('chave')`),
não de string literal dentro de `Exception`/`Controller`/`Application`.

Observação honesta sobre dívida atual (não precisa corrigir agora): o
`Auth` tem exceptions com mensagem em português hardcoded
(`InvalidCredentialsException`, `TooManyLoginAttemptsException`), e o
`APP_LOCALE` no `.env` está `en` — uma inconsistência que já existe hoje.
Código novo não deve repetir esse padrão.

---

## Parte 3 — Frontend (`frontend/src/`)

### View — `views/*.vue`, `components/*.vue`

Só exibe e coleta dado. Qualquer I/O (chamada HTTP, estado global) passa
pela store Pinia — a view nunca chama `axios`/`http.js` direto (já é o
padrão hoje, ver `docs/architecture.md`).

**Nenhuma string literal voltada ao usuário dentro do `<template>` ou
`<script>`** — todo texto vem do arquivo de strings.

### Arquivo de strings — `src/strings/pt-BR.js` (novo)

Um objeto único exportado, aninhado por tela/domínio, importado nas views:

```js
// src/strings/pt-BR.js
export const strings = {
  auth: {
    login: {
      title: 'Entrar',
      emailLabel: 'E-mail',
      passwordLabel: 'Senha',
      submit: 'Entrar',
      submitLoading: 'Entrando...',
      noAccount: 'Não tem conta?',
      registerLink: 'Cadastre-se',
    },
  },
  dashboard: {
    greeting: {
      morning: 'Bom dia',
      afternoon: 'Boa tarde',
      evening: 'Boa noite',
      subtitle: (scheduledCount, birthdayCount) =>
        `Você tem ${scheduledCount} publicações agendadas para hoje e ${birthdayCount} aniversariante.`,
    },
  },
}
```

```vue
<script setup>
// Import relativo, igual ao resto do projeto (stores, services) — não há
// alias "@/" configurado no vite.config.js.
import { strings } from '../strings/pt-BR'
</script>

<template>
  <h1>{{ strings.auth.login.title }}</h1>
</template>
```

Texto com variável vira uma função dentro do objeto (como
`greeting.subtitle` acima) em vez de concatenar string fora do arquivo de
strings. Ver `DashboardView.vue` e `src/strings/pt-BR.js` para um exemplo
real já aplicado.

**O que não entra em `src/strings/`**: valores técnicos — nomes de rota,
enums (`'owner'`, `'free'`), chaves de API. Só texto visível ao usuário.

**Dado mock/placeholder não é string de UI.** `DashboardView.vue` tem
arrays de exemplo (posts, agenda, aniversariante do dia) marcados com um
comentário `// TODO: dados de placeholder até existirem endpoints reais`
— esse conteúdo (título de um evento, nome de um aniversariante) é **dado**,
não **texto de interface**: ele vai ser inteiramente substituído quando a
API real existir, e nunca é reaproveitado por outra tela. Não precisa
entrar em `src/strings/`. A regra de "nenhuma string literal" vale pra
texto de interface — label, botão, título de seção, mensagem — que
existe independente de qual dado está sendo mostrado (ex.: o label do
card "Posts este mês" é chrome e vai pra `strings`; o valor `12` e o
título "Post do Culto de Sexta" são dado e ficam no componente).

### Store / camada de aplicação do front

Sem mudança em relação ao que já está documentado em
`docs/architecture.md`: a store Pinia (`stores/*.js`) cumpre o papel de
"application" do frontend (orquestra chamadas via `services/http.js` e
guarda estado). Não existe (nem é necessário criar agora) uma camada de
repository/service dedicada no frontend — para o tamanho atual do projeto
seria abstração sem benefício.

---

## Parte 4 — Checklist rápido (cheiro de código errado)

- Controller com `if` de regra de negócio → mover pra `Application`.
- `Application` chamando `Model::` ou `DB::` direto → mover pra
  `Repository`.
- `Services` (helper) decidindo permissão/fluxo de usuário → isso é
  `Application`, não `Services`.
- `Application` recebendo ou devolvendo `JsonResponse`/`Request` → ela não
  pode conhecer HTTP; usar DTO (`Application/Data/`).
- `Application` devolvendo array solto (`['user' => ..., 'token' => ...]`)
  pro Controller → tipar como DTO.
- `.vue` com texto em português solto no `<template>` ou `<script>` →
  mover pra `src/strings/`.
- Módulo novo → usar `Modules/Auth` como referência estrutural completa
  (`Application/`, `Application/Data/`, `Repositories/`, etc.).

---

## Parte 5 — Tabela resumo

| Camada | Responsabilidade | Pode depender de | Não pode depender de | Onde mora |
|---|---|---|---|---|
| Request | Validar entrada | — | Repository, Model, regra de negócio | `Http/Requests/` |
| Controller | Orquestrar (chamar Application, devolver Resource) | Application, Request, Resource | Repository, Model | `Http/Controllers/` |
| Application | Regra de negócio / caso de uso | Repository (interface), Services, DTO, Events, Exceptions | Model (só devolver, não consultar), Eloquent, `DB::`, HTTP (`Request`/`JsonResponse`) | `Application/` |
| DTO | Carregar dado tipado entre Controller e Application | — | Eloquent, `DB::`, HTTP | `Application/Data/` |
| Services | Helper puro/reutilizável | Nada de estado externo | Repository, Model, regra de negócio | `Services/` (módulo) ou `app/Services/` (raiz) |
| Repository | Persistir/buscar dado | Model (Eloquent) | — | `Repositories/` |
| Model | Representar a entidade | — | Regra de negócio pesada | `app/Models` ou `Modules/<X>/app/Models` |
| Resource | Formatar resposta JSON | Model | — | `Http/Resources/` |
| View (Vue) | Exibir/coletar dado | Pinia store, `src/strings/` | Axios direto, string literal | `views/`, `components/` |
| Strings | Texto centralizado | — | — | `src/strings/pt-BR.js` |
