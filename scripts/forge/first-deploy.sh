#!/usr/bin/env bash
set -Eeuo pipefail

cd /home/forge/${FORGE_SITE_PATH}

PHP_BIN="${FORGE_PHP:-php}"

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

if [ -f package-lock.json ]; then
  npm ci --no-audit --no-fund
  npm run build
fi

$PHP_BIN artisan key:generate --force
$PHP_BIN artisan migrate --force
$PHP_BIN artisan db:seed --force
$PHP_BIN artisan storage:link || true
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan queue:restart || true
