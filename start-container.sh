#!/bin/bash
set -eu

echo "SlotBook boot"
echo "MYSQLHOST=${MYSQLHOST:-missing} DB_HOST=${DB_HOST:-missing}"

export DB_HOST="${DB_HOST:-${MYSQLHOST:-}}"
export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-}}"
export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-}}"
export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"
export DB_URL="${DB_URL:-${MYSQL_URL:-${DATABASE_URL:-}}}"

rm -f bootstrap/cache/config.php bootstrap/cache/events.php bootstrap/cache/routes-v7.php bootstrap/cache/routes.php

if [ -z "${DB_HOST:-}" ] && [ -z "${DB_URL:-}" ]; then
  echo "MYSQLHOST is not on this web service. Open the web service (the crashed one) → Variables."
  echo "Add Variable Reference from MySQL: MYSQLHOST MYSQLPORT MYSQLDATABASE MYSQLUSER MYSQLPASSWORD."
  echo "Project Shared Variables do not count unless they are shared onto this service."
  exit 1
fi

echo "Connecting to MySQL host ${DB_HOST:-url}"

n=0
until php artisan migrate --force; do
  n=$((n + 1))
  if [ "$n" -ge 20 ]; then
    echo "MySQL did not accept connections."
    exit 1
  fi
  echo "Waiting for MySQL (${n}/20)..."
  sleep 3
done

php artisan db:seed --force
php artisan storage:link || true

exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1
