#!/bin/sh
set -e

cat > /var/www/html/.env <<EOF
APP_NAME="${APP_NAME:-Todo API}"
APP_ENV="${APP_ENV:-local}"
APP_DEBUG="${APP_DEBUG:-true}"
APP_URL="${APP_URL:-http://localhost:8888}"

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-todo}"
DB_USERNAME="${DB_USERNAME:-todo}"
DB_PASSWORD="${DB_PASSWORD:-todo}"

CORS_ALLOWED_ORIGINS="${CORS_ALLOWED_ORIGINS:-http://localhost:8888}"
TOKEN_TTL_DAYS="${TOKEN_TTL_DAYS:-7}"
EOF

chown www-data:www-data /var/www/html/.env
chmod 640 /var/www/html/.env

exec apache2-foreground
