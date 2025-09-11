FROM php:8.2-fpm


# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    locales \
    gnupg2 \
    libzip-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip


# Composer (copiar desde la imagen oficial de composer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# Crear usuario no-root para desarrollo (UID 1000)
RUN useradd -G www-data,root -u 1000 -m laravel


WORKDIR /var/www/html


# Copiar entrypoint y darle permisos (entrypoint monta desde host en compose)
RUN chmod +x /usr/local/bin/entrypoint.sh || true


# Exponer el socket por defecto (php-fpm ya escucha en 9000)
CMD ["/usr/local/bin/entrypoint.sh"]
