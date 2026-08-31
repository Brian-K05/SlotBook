#!/bin/bash
set -euo pipefail

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true
php artisan optimize:clear
php artisan optimize

exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1
