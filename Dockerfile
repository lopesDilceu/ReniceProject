# syntax=docker/dockerfile:1
# check=skip=SecretsUsedInArgOrEnv
#
# (The check is skipped because the Reverb app key is public: it is sent to
# every browser, not a secret.)
#
# Production image (docs/deploy.md). One image, four roles chosen by the
# command: the web server (default), `php artisan queue:work`,
# `php artisan schedule:work` and `php artisan reverb:start`.
#
# VITE_* values are compiled into the JavaScript, so they are build args.
# Without VITE_REVERB_APP_KEY the pages poll instead of using WebSockets.

ARG PHP_VERSION=8.5
ARG NODE_VERSION=24

# --- Composer dependencies --------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

# --- Frontend build ---------------------------------------------------------
# PHP and Node together: the Wayfinder plugin runs `php artisan` during the
# build to generate the typed route helpers.
FROM node:${NODE_VERSION}-bookworm-slim AS node
FROM php:${PHP_VERSION}-cli-bookworm AS assets
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
COPY --from=vendor /app/vendor ./vendor
ARG VITE_APP_NAME=Renice
ARG VITE_REVERB_APP_KEY=
ARG VITE_REVERB_HOST=
ARG VITE_REVERB_PORT=443
ARG VITE_REVERB_SCHEME=https
# Artisan only needs to boot to list the routes; no database is touched.
RUN cp .env.example .env && php artisan key:generate --force \
    && VITE_APP_NAME="$VITE_APP_NAME" VITE_REVERB_APP_KEY="$VITE_REVERB_APP_KEY" \
       VITE_REVERB_HOST="$VITE_REVERB_HOST" VITE_REVERB_PORT="$VITE_REVERB_PORT" \
       VITE_REVERB_SCHEME="$VITE_REVERB_SCHEME" npm run build

# --- Runtime ----------------------------------------------------------------
FROM php:${PHP_VERSION}-apache-bookworm AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pcntl \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite headers \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Unprivileged Apache: document root at public/, port 8080, and ownership of
# its runtime directories, so the container never runs as root.
RUN sed -ri 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf \
    && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-renice.ini"
COPY docker/apache.conf /etc/apache2/conf-enabled/zz-renice.conf
COPY docker/entrypoint.sh /usr/local/bin/renice-entrypoint

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

RUN rm -f .env \
    && mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
       storage/framework/sessions storage/framework/views storage/logs \
    && ln -s ../storage/app/public public/storage \
    && chmod +x /usr/local/bin/renice-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 8080
ENTRYPOINT ["renice-entrypoint"]
CMD ["apache2-foreground"]
