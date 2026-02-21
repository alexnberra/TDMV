#!/usr/bin/env bash
set -Eeuo pipefail

SITE_PATH="${FORGE_SITE_PATH:?FORGE_SITE_PATH is required}"

case "$SITE_PATH" in
  /*) cd "$SITE_PATH" ;;
  *) cd "/home/forge/$SITE_PATH" ;;
esac

PHP_BIN="${FORGE_PHP:-php}"

$PHP_BIN artisan down --render="errors::503" || true

# Pull latest code.
git fetch origin "${FORGE_SITE_BRANCH}"
git checkout "${FORGE_SITE_BRANCH}"
git pull --ff-only origin "${FORGE_SITE_BRANCH}"

# Backend dependencies.
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Frontend build (if package-lock exists).
if [ -f package-lock.json ]; then
  npm ci --no-audit --no-fund
  npm run build
fi

# Laravel caches and migrations.
$PHP_BIN artisan migrate --force
$PHP_BIN artisan storage:link || true
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan queue:restart || true

$PHP_BIN artisan up
