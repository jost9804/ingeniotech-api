#!/bin/bash
set -e

cat > /app/.env <<EOF
APP_NAME=${APP_NAME:-Ingeniotech}
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_KEY=${APP_KEY}
APP_TIMEZONE=America/Bogota
APP_URL=${APP_URL:-http://localhost:8000}

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=${SESSION_DRIVER:-database}
CACHE_STORE=${CACHE_STORE:-database}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}

# Almacenamiento de imágenes de productos (Supabase Storage vía S3)
PRODUCT_DISK=${PRODUCT_DISK:-public}
AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
AWS_DEFAULT_REGION=${AWS_DEFAULT_REGION}
AWS_BUCKET=${AWS_BUCKET}
AWS_ENDPOINT=${AWS_ENDPOINT}
AWS_URL=${AWS_URL}
AWS_USE_PATH_STYLE_ENDPOINT=${AWS_USE_PATH_STYLE_ENDPOINT:-true}

# Generación de descripciones con IA (Google Gemini + búsqueda web)
GEMINI_API_KEY=${GEMINI_API_KEY}
GEMINI_MODEL=${GEMINI_MODEL:-gemini-2.5-flash}

# Búsqueda de imágenes de producto (Google Custom Search)
GOOGLE_SEARCH_KEY=${GOOGLE_SEARCH_KEY}
GOOGLE_SEARCH_CX=${GOOGLE_SEARCH_CX}

SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-localhost:5173}
FRONTEND_URL=${FRONTEND_URL:-http://localhost:5173}
EOF

# migrate (sin :fresh) preserva los datos entre deploys.
php artisan migrate --force
# Solo el UserSeeder es idempotente; garantiza el usuario admin sin duplicar datos.
php artisan db:seed --class=UserSeeder --force
php artisan storage:link || true
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
