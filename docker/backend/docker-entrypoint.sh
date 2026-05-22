#!/bin/sh
set -e

# Generate .env from container environment (Cloud SQL / GCP secrets)
cat > /var/www/html/.env <<EOF
APP_NAME=${APP_NAME:-Todo API}
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

CORS_ALLOWED_ORIGINS=${CORS_ALLOWED_ORIGINS:-*}
TOKEN_TTL_DAYS=${TOKEN_TTL_DAYS:-7}
EOF

chown www-data:www-data /var/www/html/.env
chmod 640 /var/www/html/.env

exec apache2-foreground
