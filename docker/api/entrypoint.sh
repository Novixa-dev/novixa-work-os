#!/bin/sh
set -e
# Warm Laravel caches from the runtime environment (no-op for one-off commands).
if [ "$1" = "php-fpm" ]; then
  php artisan config:cache
  php artisan route:cache
  php artisan event:cache
fi
exec "$@"
