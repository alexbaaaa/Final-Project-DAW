#!/bin/sh
set -e

mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/framework/cache/data \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache

if [ "${APP_ENV:-production}" = "production" ]; then
  php artisan config:clear --ansi
  php artisan view:clear --ansi
  php artisan config:cache --ansi
  php artisan view:cache --ansi
fi

exec "$@"
