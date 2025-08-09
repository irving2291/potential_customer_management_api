#!/bin/sh
set -e

echo "==> Bootstrapping (APP_ENV=${APP_ENV:-prod})"

# Si hay DATABASE_URL, intentamos calentar caché y correr migraciones
if [ -n "${DATABASE_URL:-}" ]; then
  echo "==> DATABASE_URL detectada"

  # Warmup de caché (no hacemos fail del contenedor si falla por temas transitorios)
  php bin/console cache:clear --env=prod --no-debug --no-warmup || true
  php bin/console cache:warmup --env=prod --no-debug || true

  # Espera simple a la DB (opcional). Si tienes host/port separados, puedes usar nc.
  # Para URL completas usamos un test PDO con PHP inline:
  echo "==> Probing DB connectivity..."
  php -r '
  $url = getenv("DATABASE_URL") ?: "";
  if ($url === "") { exit(0); }
  try {
      $parts = parse_url($url);
      if (!$parts || empty($parts["scheme"])) { throw new Exception("Bad DSN"); }
      parse_str(parse_url($url, PHP_URL_QUERY) ?: "", $q);
      $db = ltrim($parts["path"] ?? "", "/");
      $dsn = ($parts["scheme"] === "postgresql" || $parts["scheme"] === "pgsql")
          ? "pgsql:host={$parts["host"]};port=".($parts["port"]??5432).";dbname={$db}".(isset($q["sslmode"])?"":";sslmode=require")
          : (($parts["scheme"] === "mysql" || $parts["scheme"] === "pdo-mysql")
              ? "mysql:host={$parts["host"]};port=".($parts["port"]??3306).";dbname={$db}"
              : null);
      if (!$dsn) { throw new Exception("Unsupported scheme"); }
      $user = urldecode($parts["user"] ?? "");
      $pass = urldecode($parts["pass"] ?? "");
      $max = 20;
      for ($i=1; $i<=$max; $i++) {
          try {
              $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT=>5]);
              echo "DB OK\n"; exit(0);
          } catch (Throwable $e) {
              echo "DB not ready (try $i/$max): ".$e->getMessage()."\n";
              sleep(2);
          }
      }
      exit(0); // no bloquear el container
  } catch (Throwable $e) { exit(0); }
  '

  echo "==> Running doctrine migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction || true
else
  echo "==> No DATABASE_URL; saltando warmup y migraciones"
fi

# Propietarios (por si el volumen monta distinto)
chown -R www-data:www-data /var/www/html || true

# Lanza supervisor (nginx + php-fpm)
exec "$@"
