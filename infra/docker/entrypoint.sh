#!/bin/sh
set -e

echo "==> Running doctrine migrations..."
php bin/console doctrine:migrations:migrate --no-interaction || true

# Ahora ejecuta supervisord (o el CMD original)
exec "$@"
