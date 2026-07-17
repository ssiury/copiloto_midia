# copiloto_midia

SaaS multi-tenant (planos Free/Pro/Owner) para ministérios de mídia de
igreja. Backend Laravel 12 organizado como **monólito modular**
(`nwidart/laravel-modules`, módulos `Auth`/`Subscription`/`App` em
`backend/Modules/`), expondo só API (`/api/v1/...`). Frontend Vue 3 SPA em
`frontend/`, consumindo a API via Axios — os dois nunca compartilham código,
só HTTP. Ver `docs/guia_projeto.md` para o plano original por entregas.

## Antes de escrever ou alterar código

Leia **`docs/convencoes-arquitetura.md`** — define as camadas obrigatórias
para código novo:
- Backend: `Request` (validação) → `Controller` (orquestra) → `Application`
  (regra de negócio, sem tocar banco) → `Repository` (única camada que fala
  com Eloquent/DB) → `Model`. `Services/` é só para helpers puros
  (formatador, algoritmo) — **não** é regra de negócio. Dado entre
  `Controller ↔ Application` sempre via **DTO** (`Application/Data/`) —
  nunca `JsonResponse`/array solto.
- Frontend: `View` (`.vue`, só exibe/coleta dado, chama a store Pinia) +
  `src/strings/pt-BR.js` — **nenhuma string literal de texto visível ao
  usuário dentro de um `.vue`**.

Ver `docs/estado-atual.md` para o estado atual real do código (o que já
existe, o que ainda é scaffold, dívidas conhecidas — inclusive o módulo
`Subscription`, que hoje está com classes faltando e não carrega). Ver
`docs/architecture.md` para o plano de extração em microserviços
(Entrega 3), um documento diferente com o mesmo tema.

## Regras de ouro (resumo)

- Controller com `if` de regra de negócio → é bug de camada, mover pra
  `Application`.
- `Application` chamando `Model::`/`DB::` direto → mover pra `Repository`.
- Helper (`Services/`) decidindo permissão/fluxo de usuário → isso é
  `Application`, não `Services`.
- `Application` recebendo/devolvendo `JsonResponse` ou array solto → usar um
  DTO tipado.
- Texto em português solto num `.vue` → mover pra `src/strings/`.
- Módulo novo → usar `Modules/Auth` como implementação de referência (ele
  segue 100% essa convenção: `Application/`, `Application/Data/`,
  `Repositories/`, etc.).
- Dado de mock/placeholder (arrays de exemplo marcados com `// TODO`,
  ainda sem endpoint real) não é string de UI — não precisa ir pra
  `src/strings/`, só texto de interface (label, botão, mensagem) vai.
