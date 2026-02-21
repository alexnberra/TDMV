#!/usr/bin/env bash
set -Eeuo pipefail

SITE_PATH="${FORGE_SITE_PATH:?FORGE_SITE_PATH is required}"

case "$SITE_PATH" in
  /*) cd "$SITE_PATH" ;;
  *) cd "/home/forge/$SITE_PATH" ;;
esac

PHP_BIN="${FORGE_PHP:-php}"
REQUIRED_NODE="20.19.0"

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

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

if [ -f package-lock.json ]; then
  ensure_node_runtime
  build_frontend_assets
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
