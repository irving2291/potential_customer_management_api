FROM php:8.3-fpm-alpine

# Instala dependencias de sistema
RUN apk add --no-cache --update \
    nginx \
    supervisor \
    git \
    unzip \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    rabbitmq-c-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    freetype-dev \
    postgresql-dev \
    openssl-dev \
    bash \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache \
    && pecl install amqp \
    && docker-php-ext-enable amqp

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Crea carpetas necesarias
RUN mkdir -p /var/log/supervisor /run/nginx /var/www/html

# Copia la app
WORKDIR /var/www/html
COPY ./app /var/www/html

# Instala dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data /var/www/html

# Copia configuraciones
COPY ./infra/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./infra/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./infra/supervisord/supervisord.conf /etc/supervisord.conf

# Exponer puerto del contenedor
EXPOSE 80

# Comando principal
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
