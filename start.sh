#!/usr/bin/env sh
set -eu

export DB_HOST="${DB_HOST:-${MYSQLHOST:-}}"
export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-}}"
export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-}}"
export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"
export DB_URL="${DB_URL:-${MYSQL_URL:-${DATABASE_URL:-}}}"

php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
