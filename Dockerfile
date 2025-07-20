FROM php:8.3-fpm

# Instalar dependencias de sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    librabbitmq-dev \
    libssl-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

# --- AMQP ---
RUN pecl install amqp \
    && docker-php-ext-enable amqp

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia los archivos del proyecto
COPY ./app /var/www/html

# Instala las dependencias de PHP
RUN composer install --no-interaction

# Permisos para desarrollo
RUN chown -R www-data:www-data /var/www/html/var

# Expone el puerto para PHP-FPM (normalmente usado por nginx)
EXPOSE 9000

CMD ["php-fpm"]
