FROM php:8.3-fpm-alpine

# Dependencias del sistema y extensiones PHP (incluye ca-certificates para TLS)
RUN apk add --no-cache \
    nginx supervisor git unzip icu-dev libzip-dev oniguruma-dev rabbitmq-c-dev \
    bash autoconf build-base libtool postgresql-dev openssl-dev \
    freetype-dev libpng-dev libjpeg-turbo-dev libwebp-dev libxpm-dev zlib-dev netcat-openbsd \
    ca-certificates \
 && update-ca-certificates \
 && docker-php-ext-install intl pdo pdo_pgsql pdo_mysql zip opcache pcntl \
 && pecl install amqp \
 && docker-php-ext-enable amqp \
 # Config AMQP SSL: apunta al bundle de CAs del sistema
 && printf "amqp.cacert=/etc/ssl/certs/ca-certificates.crt\n" \
      > /usr/local/etc/php/conf.d/99-amqp-ssl.ini

# (opcional) Si prefieres usar el CA específico de Amazon, cópialo y referencia esa ruta:
# COPY infra/certs/amazonmq-ca.pem /etc/ssl/certs/amazonmq-ca.pem
# RUN printf "amqp.cacert=/etc/ssl/certs/amazonmq-ca.pem\n" > /usr/local/etc/php/conf.d/99-amqp-ssl.ini

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Vars base (no secrets aquí)
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=prod \
    APP_DEBUG=0 \
    SYMFONY_SKIP_AUTO_RUN=1

WORKDIR /var/www/html

# 1) Copia Composer files primero para aprovechar cache
COPY ./app/composer.json ./app/composer.lock ./

# 2) Instala dependencias sin scripts (no cache:clear en build)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-ansi --no-scripts

# 3) Copia el resto del código de la app
COPY ./app /var/www/html

# 4) Re-optimiza autoloads (sin scripts)
RUN composer dump-autoload --classmap-authoritative --no-dev --no-interaction \
 && chown -R www-data:www-data /var/www/html

# Configuración nginx y supervisor
COPY ./infra/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./infra/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./infra/supervisord/supervisord.conf /etc/supervisord.conf

# Entrypoint
COPY ./infra/docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
