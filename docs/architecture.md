# Arquitetura

## Decisão: monólito modular

O projeto começa como um monólito modular em Laravel, usando `nwidart/laravel-modules`
para separar o código em módulos com fronteiras claras: `Auth`, `Subscription`, `App`.

Cada módulo tem suas próprias rotas, controllers, services e migrations. A comunicação
entre módulos acontece via interfaces/contratos internos (Service classes injetadas),
não via HTTP — até que haja motivo real para extrair um módulo como serviço separado.

Regra aplicada desde a Entrega 3: nenhum módulo depende da classe concreta de um
service de outro módulo — sempre de uma interface em `Modules\<Módulo>\Contracts`,
resolvida via container (bind feito no `<Módulo>ServiceProvider`). Isso é o que
permite, no futuro, trocar o `bind` de uma implementação local por um client HTTP
sem tocar em quem consome.

## Plano de extração para microserviços

### Ordem de extração

1. **`Subscription` sai primeiro.** É o módulo com menor acoplamento: sua única
   dependência de outro módulo é escutar o evento `Auth\Events\UserRegistered`
   (dado simples, serializável) para criar a assinatura Free. Não depende de
   nenhuma classe concreta de `Auth` ou `App`. É também o módulo que vai crescer
   em complexidade primeiro (cobrança, webhooks de gateway), o que justifica
   isolá-lo com seu próprio ciclo de deploy.
2. **`App` sai em seguida**, quando o módulo tiver features reais que consomem
   recursos limitados por plano (hoje ele só verifica limites via
   `PlanServiceInterface::hasReachedLimit`, sem nenhum recurso consumível
   implementado ainda).
3. **`Auth` permanece no monólito por último** (ou nunca sai): é a dependência
   transitiva de todos os outros módulos (autenticação de todo request) e sua
   extração implica decisões maiores (token compartilhado entre serviços,
   introspecção de sessão), então só compensa depois que os outros dois já
   forem serviços separados e a necessidade for concreta.

### Mecanismo de transição

Enquanto tudo roda no mesmo processo, a "chamada de serviço" é só uma injeção de
interface (`app()->bind(XServiceInterface::class, XService::class)`). Para extrair
um módulo:

1. Mover a pasta do módulo para um repo/serviço próprio (rotas, controllers,
   models, migrations já estão isolados dentro dele).
2. Trocar o `bind` da interface, no monólito que ficou para trás, por uma
   implementação `Http<Módulo>Service` que faz a chamada HTTP real usando o
   contrato abaixo — os consumidores (controllers, listeners, commands) não
   mudam, pois só conhecem a interface.
3. As rotas internas já existentes (protegidas por `X-Internal-Key`, ver
   abaixo) passam a ser o próprio endpoint HTTP do novo serviço, sem precisar
   ser criadas do zero.

### Contrato de API interna por módulo

Toda rota interna é autenticada por API key (header `X-Internal-Key`, verificado
pelo middleware `internal.key` / `App\Http\Middleware\VerifyInternalApiKey`), não
por sessão de usuário — é a forma como um serviço chamaria outro serviço-a-serviço.

**`Subscription`** (`SubscriptionServiceInterface`, hoje resolvida localmente por
`Modules\Subscription\Services\SubscriptionService`; exposta em
`POST /api/v1/internal/subscriptions/...`):
- `createSubscription(User $user, Plan $plan): UserSubscription` —
  `POST /v1/internal/subscriptions`
- `cancelSubscription(UserSubscription $subscription): UserSubscription` —
  `POST /v1/internal/subscriptions/{subscription}/cancel`
- `renewSubscription(UserSubscription $subscription): UserSubscription` —
  `POST /v1/internal/subscriptions/{subscription}/renew`
- `changePlan(UserSubscription $subscription, Plan $newPlan): UserSubscription` —
  `POST /v1/internal/subscriptions/{subscription}/change-plan`
- `registerPayment(UserSubscription $subscription, PaymentData $payment): Payment` —
  `POST /v1/internal/subscriptions/{subscription}/payments`
  (formato de `PaymentData` já pensado para virar o payload de um webhook de
  gateway — Stripe, Mercado Pago, Asaas ou Pagar.me — sem mudar a assinatura)

Além disso, `PlanServiceInterface` (limites/uso: `hasReachedLimit`,
`canUseFeature`, `isUnlimited`, `summaryFor`) seria exposta por rotas internas
equivalentes no momento em que `App` (consumidor de `plan.limit`) for extraído
para outro processo.

**`Auth`** (`AuthServiceInterface`, hoje resolvida localmente por
`Modules\Auth\Services\AuthService`): quando `Subscription`/`App` forem
serviços separados, precisarão validar o usuário autenticado sem acesso direto
à tabela `users`. O contrato interno a expor nesse momento é:
- `GET /v1/internal/users/{id}` — dados básicos do usuário (id, email,
  user_type), protegido por `X-Internal-Key`
- Validação de token Sanctum via endpoint interno (ou migração para um formato
  de token verificável sem round-trip, ex. JWT assinado) — decisão a tomar
  quando a extração de `Auth` for concreta, não antes.

**`App`**: ainda não tem rotas ou regras de negócio reais (só o CRUD placeholder
gerado pelo `laravel-modules`), então não há contrato a documentar além de, no
futuro, consumir `Subscription` via `plan.limit` apontando para o serviço
extraído em vez do bind local.
