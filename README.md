# copiloto_midia

Base de um SaaS multi-tenant (Free/Pro/Owner), estruturado como monólito modular
em Laravel (módulos `Auth`, `Subscription`, `App` via `nwidart/laravel-modules`)
com frontend em Vue 3.

Ver [`docs/architecture.md`](docs/architecture.md) para a decisão de arquitetura.

## Stack

- Backend: PHP 8.3, Laravel 12, PostgreSQL 16, Redis, Laravel Sanctum, Pest
- Frontend: Vue 3 (Composition API), Vue Router, Pinia, Bootstrap 5, Axios
- Infra: Docker + Docker Compose

## Pré-requisitos

- Docker e Docker Compose instalados

## Subindo o ambiente

1. Copie o `.env.example` da raiz para `.env` e ajuste as variáveis se necessário:

   ```bash
   cp .env.example .env
   ```

2. Copie também o `.env.example` do backend (usado pelo container `app` e por comandos
   `artisan` executados dentro dele):

   ```bash
   cp backend/.env.example backend/.env
   ```

   Não é necessário instalar dependências ou gerar a `APP_KEY` manualmente: o
   container `app` faz isso sozinho na primeira vez que sobe (via
   `backend/docker/php/entrypoint.sh`), rodando `composer install` e
   `php artisan key:generate` se necessário.

3. Suba os containers:

   ```bash
   docker compose up -d
   ```

   Isso sobe 5 serviços: `app` (PHP-FPM), `nginx`, `postgres`, `redis` e `frontend`
   (Vite dev server). Na primeira subida, `app` pode demorar um pouco mais enquanto
   instala as dependências do Composer e `frontend` enquanto instala as do npm.

4. Rode as migrations:

   ```bash
   docker compose exec app php artisan migrate
   ```

## Endereços

- Backend (API): http://localhost:8000/api/v1/health
- Frontend: http://localhost:5173

## Variáveis de ambiente

### Raiz (`.env`, lido pelo `docker-compose.yml`)

| Variável | Descrição |
|---|---|
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Credenciais do Postgres |
| `NGINX_PORT`, `POSTGRES_PORT`, `REDIS_PORT`, `VITE_PORT` | Portas expostas no host |
| `FRONTEND_URL` | Origem liberada no CORS do backend |
| `VITE_API_URL` | URL da API usada pelo frontend |

### Backend (`backend/.env`)

Segue o padrão do Laravel, com `DB_*` apontando para o container `postgres` e
`REDIS_*` apontando para o container `redis` (já pré-configurado no `.env.example`).

### Frontend (`frontend/.env`)

| Variável | Descrição |
|---|---|
| `VITE_API_URL` | Base URL da API consumida pelo Axios |

## Módulos

Os módulos do backend vivem em `backend/Modules/`:

```bash
docker compose exec app php artisan module:list
```

## Testes

```bash
docker compose exec app php artisan test
```

## Parando o ambiente

```bash
docker compose down
```
