#!/bin/bash
set -e

# Проверяем, установлен ли Laravel
if [ ! -f /var/www/html/artisan ]; then
    echo "Laravel not found. Creating new Laravel project..."
    composer create-project --prefer-dist laravel/laravel .
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
else
    echo "Laravel already installed. Running composer install..."
    composer install --no-interaction
fi

# Запускаем Apache
exec "$@"