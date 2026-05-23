# Backend (PHP API) — build from repo root:
#   docker build -f backend/Dockerfile .

FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

FROM php:8.2-apache-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY backend/apache.conf /etc/apache2/sites-available/000-default.conf
COPY backend/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.json ./composer.json
COPY --from=vendor /app/composer.lock ./composer.lock
COPY public ./public
COPY routes ./routes
COPY src ./src

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
