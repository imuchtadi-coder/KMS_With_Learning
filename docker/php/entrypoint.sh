#!/usr/bin/env sh
set -eu

cd /var/www

mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

until pg_isready \
    -h "${DB_HOST:-postgres}" \
    -p "${DB_PORT:-5432}" \
    -U "${DB_USERNAME:-kitsuluh}" \
    -d "${DB_DATABASE:-kitsuluh}" >/dev/null 2>&1; do
    echo "Menunggu PostgreSQL..."
    sleep 2
done

php artisan package:discover --ansi
php artisan storage:link --force
php artisan config:cache
php artisan view:cache

exec "$@"
