# Novixa WorkOS — Production PHP 8.4-FPM Dockerfile
FROM php:8.4-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    icu-dev \
    postgresql-dev \
    sqlite-dev \
    libzip-dev \
    oniguruma-dev \
    linux-headers \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/include/postgresql/ \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        pdo_sqlite \
        intl \
        bcmath \
        opcache \
        zip \
        pcntl \
        mbstring

# Configure OPcache for high-throughput production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Security & hardening PHP settings
RUN { \
    echo 'expose_php=Off'; \
    echo 'upload_max_filesize=50M'; \
    echo 'post_max_size=55M'; \
    echo 'memory_limit=512M'; \
    echo 'max_execution_time=60'; \
} > /usr/local/etc/php/conf.d/novixa-security.ini

# Get Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer definition first for Docker layer caching
COPY apps/api/composer.json apps/api/composer.lock ./

# Install dependencies without dev
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application source
COPY apps/api/ ./

# Finish autoloader and directory permissions
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
