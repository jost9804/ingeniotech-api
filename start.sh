#!/bin/bash
set -e

cat > /app/.env <<EOF
APP_NAME=Laravel
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_KEY=${APP_KEY}
APP_TIMEZONE=America/Bogota

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-localhost:5173}
FRONTEND_URL=${FRONTEND_URL:-http://localhost:5173}
EOF

php artisan migrate:fresh --force --seed
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
