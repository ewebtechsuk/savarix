#!/bin/bash
set -euo pipefail

PHP83="/opt/alt/php83/usr/bin/php"
ARTISAN="$PHP83 artisan"

cd "${HOME}/domains/savarix.com/laravel_app"

echo "[hostinger] Using PHP binary: ${PHP83}"
${PHP83} -v

if command -v composer >/dev/null 2>&1 && [ -f "composer.json" ]; then
  echo "[hostinger] Installing/updating Composer dependencies (PHP 8.3)"
  COMPOSER_BIN=$(command -v composer)
  ${PHP83} "${COMPOSER_BIN}" install --no-interaction --prefer-dist
else
  echo "[hostinger] Skipping Composer install (composer.json missing or composer not available)"
fi

echo "[hostinger] Clearing caches"
${ARTISAN} config:clear
${ARTISAN} cache:clear
${ARTISAN} route:clear
${ARTISAN} view:clear

echo "[hostinger] Running central migrations"
${ARTISAN} migrate --force

echo "[hostinger] Running tenant migrations"
${ARTISAN} tenants:migrate --force

echo "[hostinger] Rebuilding caches"
${ARTISAN} config:cache
${ARTISAN} route:cache
${ARTISAN} view:cache

echo "[hostinger] Deployment completed successfully."
