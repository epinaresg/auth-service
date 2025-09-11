#!/bin/sh
set -e

# Si vendor no existe, instalar dependencias (solo en primera creación)
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "-> Installing composer dependencies"
    composer install --no-interaction --prefer-dist --optimize-autoloader || true
fi

# Permisos sobre storage y cache
chown -R laravel:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Ejecutar php-fpm
php-fpm
