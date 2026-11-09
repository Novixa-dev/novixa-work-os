# syntax=docker/dockerfile:1.7
# Novixa WorkOS — API image (Laravel 13 on PHP 8.4-FPM, Alpine)

FROM composer:2.8 AS vendor
WORKDIR /app
COPY apps/api/composer.json apps/api/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --ignore-platform-req=php
COPY apps/api/ ./
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts

FROM php:8.4-fpm-alpine AS runtime
COPY --from=ghcr.io/mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN apk add --no-cache curl tzdata \
    && install-php-extensions pdo_pgsql intl bcmath opcache zip pcntl

RUN { \
      echo 'opcache.enable=1'; \
      echo 'opcache.memory_consumption=192'; \
      echo 'opcache.interned_strings_buffer=16'; \
      echo 'opcache.max_accelerated_files=20000'; \
      echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini \
 && { \
      echo 'expose_php=Off'; \
      echo 'upload_max_filesize=26M'; \
      echo 'post_max_size=28M'; \
      echo 'memory_limit=256M'; \
      echo 'max_execution_time=60'; \
      echo 'date.timezone=UTC'; \
    } > /usr/local/etc/php/conf.d/novixa.ini

WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app ./
COPY docker/api/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/private bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 9000
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
