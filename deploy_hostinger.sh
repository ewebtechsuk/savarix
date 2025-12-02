#!/bin/bash
# Savarix – Hostinger Deploy Script (PHP 8.3)

set -euo pipefail

###############################################
# RESOLVE CORRECT PHP 8.3 BINARY
###############################################
PHP="/opt/alt/php83/usr/bin/php"

if [ ! -x "$PHP" ]; then
  # Fallbacks if Hostinger moves things around
  if command -v php83 >/dev/null 2>&1; then
    PHP="$(command -v php83)"
  elif command -v php >/dev/null 2>&1; then
    PHP="$(command -v php)"
  else
    echo "::error title=PHP binary not found::Could not find PHP 8.3 binary on Hostinger."
    exit 1
  fi
fi

COMPOSER_BIN="/usr/bin/composer"

echo "[deploy_hostinger] Using PHP: $PHP"
$PHP -v || true

###############################################
# PATHS
###############################################
HOSTINGER_USER="${HOSTINGER_USER:?HOSTINGER_USER is not set}"

APP_DIR="/home/${HOSTINGER_USER}/domains/savarix.com/laravel_app"
PUBLIC_HTML="/home/${HOSTINGER_USER}/domains/savarix.com/public_html"

echo "[deploy_hostinger] App directory: $APP_DIR"
echo "[deploy_hostinger] Public HTML:   $PUBLIC_HTML"

if [ ! -d "$APP_DIR" ]; then
  echo "::error title=Missing app dir::$APP_DIR does not exist."
  exit 1
fi

cd "$APP_DIR"

###############################################
# ENSURE COMPOSER.JSON EXISTS
###############################################
if [ ! -f "composer.json" ]; then
  echo "::error title=Missing composer.json::composer.json not found in $APP_DIR."
  exit 1
fi

###############################################
# INSTALL COMPOSER DEPENDENCIES (NO-DEV)
###############################################
echo "[deploy_hostinger] Installing Composer dependencies (no-dev)"
$PHP "$COMPOSER_BIN" install \
  --no-dev \
  --optimize-autoloader \
  --no-interaction \
  --prefer-dist

###############################################
# CLEAR CACHES SAFELY
###############################################
echo "[deploy_hostinger] Clearing old caches"
$PHP artisan config:clear || true
$PHP artisan cache:clear || true
$PHP artisan route:clear || true
$PHP artisan view:clear || true

###############################################
# RUN MIGRATIONS
###############################################
echo "[deploy_hostinger] Running migrations"
$PHP artisan migrate --force || true

###############################################
# REBUILD OPTIMIZED CACHES
###############################################
echo "[deploy_hostinger] Rebuilding caches"
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

###############################################
# SYMLINK LARAVEL public → public_html
###############################################
echo "[deploy_hostinger] Ensuring public_html points to Laravel public"

# remove existing folder/symlink then recreate
rm -rf "$PUBLIC_HTML"
ln -s "$APP_DIR/public" "$PUBLIC_HTML"

echo "[deploy_hostinger] Deployment complete!"
