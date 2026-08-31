#!/bin/bash
set -euo pipefail

php artisan config:clear
php artisan optimize:clear

if [ -z "${DB_HOST:-}${MYSQLHOST:-}${DB_URL:-}${MYSQL_URL:-}${DATABASE_URL:-}" ]; then
  echo "No MySQL host on this service. Railway does not copy plugin vars onto the web service by itself."
  echo "Web service → Variables → Variable Reference → add MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD from the MySQL service. Then redeploy."
  exit 1
fi

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
