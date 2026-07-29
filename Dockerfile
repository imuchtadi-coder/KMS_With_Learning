FROM node:22-alpine AS frontend

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build


FROM composer:2 AS composer-dependencies

WORKDIR /build

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader


FROM php:8.4-fpm-bookworm AS app

WORKDIR /var/www

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ffmpeg \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/production.ini /usr/local/etc/php/conf.d/99-kitsuluh.ini
COPY --chown=www-data:www-data . .
COPY --from=composer-dependencies --chown=www-data:www-data /build/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /build/public/build ./public/build

RUN mkdir -p \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && php artisan package:discover --ansi

CMD ["php-fpm"]


FROM nginx:1.27-alpine AS web

WORKDIR /var/www

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=frontend /build/public ./public

RUN mkdir -p /var/www/storage/app/public \
    && ln -sfn ../storage/app/public /var/www/public/storage
