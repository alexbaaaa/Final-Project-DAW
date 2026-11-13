#!/bin/sh
set -e

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ "$1" = "apache2-foreground" ] && [ "${APP_ENV:-production}" = "production" ] && [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is empty. Set APP_KEY in the production env file before starting Laravel."
    exit 1
fi

if [ "$1" = "apache2-foreground" ]; then
    php artisan package:discover --ansi
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
