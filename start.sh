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

if [ -z "${BREVO_KEY:-}" ] || [ "${BREVO_KEY}" = "null" ]; then
  if [ -n "${MAIL_PASSWORD:-}" ] && [ "${MAIL_PASSWORD#xkeysib-}" != "${MAIL_PASSWORD}" ]; then
    export BREVO_KEY="$MAIL_PASSWORD"
  else
    echo "WARN: BREVO_KEY is missing. Railway Hobby blocks SMTP, so booking notes will not reach inboxes."
  fi
fi

echo "MYSQLHOST=${MYSQLHOST:-missing}"
echo "DB_HOST=${DB_HOST:-missing}"
echo "DB_DATABASE=${DB_DATABASE:-missing}"
if [ -n "${BREVO_KEY:-}" ] && [ "${BREVO_KEY}" != "null" ]; then
  echo "BREVO_KEY=set"
else
  echo "BREVO_KEY=missing"
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
