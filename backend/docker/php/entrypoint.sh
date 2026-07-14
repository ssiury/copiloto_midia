#!/bin/sh
set -e

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

chown -R www-data:www-data storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --ansi --force
fi

exec "$@"
