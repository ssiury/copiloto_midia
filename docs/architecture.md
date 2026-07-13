# Arquitetura

## Decisão: monólito modular

O projeto começa como um monólito modular em Laravel, usando `nwidart/laravel-modules`
para separar o código em módulos com fronteiras claras: `Auth`, `Subscription`, `App`.

Cada módulo tem suas próprias rotas, controllers, services e migrations. A comunicação
entre módulos acontece via interfaces/contratos internos (Service classes injetadas),
não via HTTP — até que haja motivo real para extrair um módulo como serviço separado.

> O plano de extração para microserviços e os contratos de API interna de cada módulo
> serão documentados aqui na Entrega 3.
