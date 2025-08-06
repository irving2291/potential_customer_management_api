FROM php:8.3-fpm-alpine

# Dependencias para build y extensiones
RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    unzip \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    rabbitmq-c-dev \
    bash \
    autoconf \
    build-base \
    libtool \
    postgresql-dev \
    openssl-dev \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    zlib-dev \
    && docker-php-ext-install intl pdo pdo_pgsql pdo_mysql zip opcache \
    && pecl install amqp \
    && docker-php-ext-enable amqp

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App setup
WORKDIR /var/www/html
COPY ./app /var/www/html

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV SYMFONY_SKIP_AUTO_RUN=1

# Instala Composer sin dev y sin auto-scripts
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php bin/console cache:clear --no-warmup \
    && php bin/console cache:warmup \
    && chown -R www-data:www-data /var/www/html

# Configuración nginx y supervisor
COPY ./infra/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./infra/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./infra/supervisord/supervisord.conf /etc/supervisord.conf

# Exponer puerto HTTP
EXPOSE 80

# Ejecutar ambos servicios
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
