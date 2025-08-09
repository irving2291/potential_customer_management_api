FROM php:8.3-fpm-alpine

# Dependencias del sistema y extensiones PHP
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
    netcat-openbsd \
 && docker-php-ext-install intl pdo pdo_pgsql pdo_mysql zip opcache \
 && pecl install amqp \
 && docker-php-ext-enable amqp

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

# 4) Re-optimiza autoloads (sin scripts) y permisos
RUN composer dump-autoload --classmap-authoritative --no-dev --no-interaction \
 && chown -R www-data:www-data /var/www/html

# === PHP-FPM: permitir variables de entorno en runtime ===
# clear_env=no hace que php-fpm herede las env del proceso padre (supervisord)
# Además, exponemos explícitamente algunas env comunes (añade las que uses)
RUN set -eux; \
    { \
      echo 'clear_env = no'; \
      echo 'env[APP_ENV] = $APP_ENV'; \
      echo 'env[APP_DEBUG] = $APP_DEBUG'; \
      echo 'env[APP_SECRET] = $APP_SECRET'; \
      echo 'env[DATABASE_URL] = $DATABASE_URL'; \
      # Si usas más (MAILER_DSN, MESSENGER_TRANSPORT_DSN, etc), añádelas:
      # echo "env[MAILER_DSN] = \$MAILER_DSN"; \
    } > /usr/local/etc/php-fpm.d/zz-env.conf

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
