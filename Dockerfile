# ---- Stage 1: PHP dependencies ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# --ignore-platform-reqs: this stage is only for downloading packages; the runtime image has the real PHP extensions
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts --ignore-platform-reqs

# ---- Stage 2: runtime (nginx + php-fpm) ----
FROM serversideup/php:8.5-fpm-nginx
WORKDIR /var/www/html

# Uncomment if composer.json needs extensions that this image lacks:
# USER root
# RUN install-php-extensions zip gd intl
# USER www-data

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor

RUN mkdir -p storage/app/private storage/app/public \
        storage/framework/cache storage/framework/sessions storage/framework/views \
        storage/logs bootstrap/cache