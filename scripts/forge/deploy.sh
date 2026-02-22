#!/usr/bin/env bash
set -Eeuo pipefail

SITE_PATH="${FORGE_SITE_PATH:?FORGE_SITE_PATH is required}"

PHP_BIN="${FORGE_PHP:-php}"
REQUIRED_NODE="20.19.0"

resolve_release_path() {
  if [ -n "${FORGE_RELEASE_DIRECTORY:-}" ] && [ -f "${FORGE_RELEASE_DIRECTORY}/artisan" ]; then
    cd "${FORGE_RELEASE_DIRECTORY}"

    return
  fi

  local base_path="$SITE_PATH"
  case "$base_path" in
    /*) ;;
    *) base_path="/home/forge/$base_path" ;;
  esac

  if [ -f "$base_path/artisan" ]; then
    cd "$base_path"

    return
  fi

  if [ -f "$base_path/current/artisan" ]; then
    cd "$base_path/current"

    return
  fi

  echo "Unable to locate Laravel release directory for FORGE_SITE_PATH=$SITE_PATH"
  exit 1
}

ensure_node_runtime() {
  local current_node="0.0.0"

  if command -v node >/dev/null 2>&1; then
    current_node="$(node -v | sed 's/^v//')"
  fi

  if ! printf '%s\n%s\n' "$REQUIRED_NODE" "$current_node" | sort -V -C; then
    export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"

    if [ -s "$NVM_DIR/nvm.sh" ]; then
      # shellcheck disable=SC1090
      . "$NVM_DIR/nvm.sh"
      nvm install 22 --latest-npm >/dev/null
      nvm use 22 >/dev/null
      current_node="$(node -v | sed 's/^v//')"
    fi
  fi

  if ! printf '%s\n%s\n' "$REQUIRED_NODE" "$current_node" | sort -V -C; then
    echo "Node.js ${REQUIRED_NODE}+ is required. Current: ${current_node}."
    echo "Set your Forge Node version to 22.x and redeploy."
    exit 1
  fi
}

build_frontend_assets() {
  npm ci --no-audit --no-fund --include=optional

  if ! npm run build; then
    echo "Initial frontend build failed; retrying clean install for optional native dependencies..."
    rm -rf node_modules
    npm install --no-audit --no-fund --include=optional
    npm run build
  fi
}

ensure_laravel_paths() {
  mkdir -p bootstrap/cache
  mkdir -p resources/views
  mkdir -p storage/app/public storage/app/private
  mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views
  mkdir -p storage/logs
}

purge_bootstrap_cache() {
  rm -f bootstrap/cache/*.php || true
}

clear_laravel_caches() {
  if $PHP_BIN artisan optimize:clear; then
    return
  fi

  echo "optimize:clear failed; running selective clears and continuing..."
  $PHP_BIN artisan config:clear || true
  $PHP_BIN artisan cache:clear || true
  $PHP_BIN artisan event:clear || true
  $PHP_BIN artisan route:clear || true
  $PHP_BIN artisan view:clear || true
}

resolve_release_path
ensure_laravel_paths
purge_bootstrap_cache

$PHP_BIN artisan down --render="errors::503" || true

# Pull latest code.
git fetch origin "${FORGE_SITE_BRANCH}"
git checkout "${FORGE_SITE_BRANCH}"
git pull --ff-only origin "${FORGE_SITE_BRANCH}"
ensure_laravel_paths
purge_bootstrap_cache

# Backend dependencies.
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Frontend build (if package-lock exists).
if [ -f package-lock.json ]; then
  ensure_node_runtime
  build_frontend_assets
fi

# Laravel caches and migrations.
clear_laravel_caches
$PHP_BIN artisan tdmv:reconcile-migrations
$PHP_BIN artisan migrate --force
$PHP_BIN artisan tdmv:ensure-login-users --mode="${TDMV_SEED_MODE:-live}"
$PHP_BIN artisan tdmv:set-login-password admin@tribe.gov password
$PHP_BIN artisan storage:link || true
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan queue:restart || true

$PHP_BIN artisan up
