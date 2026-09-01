#!/usr/bin/env sh
set -eu

echo "SlotBook docker boot"

if [ -n "${MYSQLHOST:-}" ]; then
  export DB_HOST="$MYSQLHOST"
fi
if [ -n "${MYSQLPORT:-}" ]; then
  export DB_PORT="$MYSQLPORT"
fi
if [ -n "${MYSQLDATABASE:-}" ]; then
  export DB_DATABASE="$MYSQLDATABASE"
fi
if [ -n "${MYSQLUSER:-}" ]; then
  export DB_USERNAME="$MYSQLUSER"
fi
if [ -n "${MYSQLPASSWORD:-}" ]; then
  export DB_PASSWORD="$MYSQLPASSWORD"
fi
if [ -n "${MYSQL_URL:-}" ]; then
  export DB_URL="$MYSQL_URL"
elif [ -n "${DATABASE_URL:-}" ]; then
  export DB_URL="$DATABASE_URL"
fi

if [ -n "${MAIL_USERNAME:-}" ] && [ "${MAIL_USERNAME}" != "null" ]; then
  export MAIL_MAILER=smtp
  if [ -z "${MAIL_HOST:-}" ] || [ "${MAIL_HOST}" = "127.0.0.1" ] || [ "${MAIL_HOST}" = "localhost" ] || [ "${MAIL_HOST}" = "null" ]; then
    export MAIL_HOST=smtp-relay.brevo.com
  fi
  if [ -z "${MAIL_PORT:-}" ] || [ "${MAIL_PORT}" = "2525" ] || [ "${MAIL_PORT}" = "null" ]; then
    export MAIL_PORT=587
  fi
  unset MAIL_SCHEME || true
  if [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    export MAIL_EHLO_DOMAIN="${RAILWAY_PUBLIC_DOMAIN}"
  fi
else
  echo "WARN: MAIL_USERNAME is missing. Booking notes will not reach inboxes."
fi

echo "MYSQLHOST=${MYSQLHOST:-missing}"
echo "DB_HOST=${DB_HOST:-missing}"
echo "DB_DATABASE=${DB_DATABASE:-missing}"
echo "MAIL_MAILER=${MAIL_MAILER:-log}"
echo "MAIL_HOST=${MAIL_HOST:-unset}"
echo "MAIL_PORT=${MAIL_PORT:-unset}"
if [ -n "${MAIL_USERNAME:-}" ] && [ "${MAIL_USERNAME}" != "null" ]; then
  echo "MAIL_USERNAME=set"
else
  echo "MAIL_USERNAME=missing"
fi
echo "MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-unset}"

if [ -z "${MYSQLHOST:-}" ] && [ -z "${MYSQL_URL:-}" ] && [ -z "${DATABASE_URL:-}" ]; then
  echo "FATAL: MYSQLHOST is not on this web service."
  echo "Click the SlotBook GitHub service (not Shared Variables) → Variables → {} → MySQL → MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD."
  env | awk -F= '/^(MYSQL|DB_)/ { print $1 }'
  exit 1
fi

rm -f bootstrap/cache/config.php bootstrap/cache/events.php bootstrap/cache/routes-v7.php bootstrap/cache/routes.php
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
