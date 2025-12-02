#!/bin/bash
# Savarix – Hostinger Deploy Script (PHP 8.3)

set -e

###############################################
# CONFIG: Correct Hostinger PHP 8.3 binary
###############################################
PHP="/opt/alt/php83/usr/bin/php"
COMPOSER="/opt/alt/php83/usr/bin/php /usr/bin/composer"

# If Hostinger changed paths, fallback
if [ ! -f "/opt/alt/php83/usr/bin/php" ]; then
    PHP="/usr/bin/php83"
    COMPOSER="$PHP /usr/bin/composer"
fi

echo "[deploy_hostinger] Using PHP: $PHP"
$PHP -v

###############################################
# PATHS
###############################################
HOSTINGER_USER="${HOSTINGER_USER}"
APP_DIR="/home/${HOSTINGER_USER}/domains/savarix.com/laravel_app"
PUBLIC_HTML="/home/${HOSTINGER_USER}/domains/savarix.com/public_html"

echo "[deploy_hostinger] App directory: $APP_DIR"
echo "[deploy_hostinger] Public HTML: $PUBLIC_HTML"

cd "$APP_DIR"

###############################################
# FIX: Ensure composer.json exists
###############################################
if [ ! -f "composer.json" ]; then
  echo "::error title=Missing composer.json::composer.json not found in $APP_DIR."
  exit 1
fi

###############################################
# INSTALL COMPOSER DEPENDENCIES (NO-DEV)
###############################################
echo "[deploy_hostinger] Installing Composer dependencies"
$COMPOSER install --no-dev --optimize-autoloader --no-interaction --prefer-dist

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
# SYMLINK PUBLIC/LARAVEL_APP/PUBLIC → public_html
###############################################
echo "[deploy_hostinger] Ensuring public_html points to Laravel public"
rm -rf "$PUBLIC_HTML"
ln -s "$APP_DIR/public" "$PUBLIC_HTML"

echo "[deploy_hostinger] Deployment complete!"
