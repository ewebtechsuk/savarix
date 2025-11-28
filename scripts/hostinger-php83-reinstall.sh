#!/usr/bin/env bash
# Manual helper for Hostinger recovery with PHP 8.3.
# Not intended for CI/CD or unattended execution.
# Run from the Laravel project root (~/domains/savarix.com/laravel_app).

set -euo pipefail

PHP_BIN="/opt/alt/php83/usr/bin/php"
APP_DIR="$(pwd)"

info() {
  printf "[hostinger-php83] %s\n" "$1"
}

# Decide which composer to use (prefer local composer.phar).
if [[ -f "$APP_DIR/composer.phar" ]]; then
  COMPOSER_CMD=("$PHP_BIN" "$APP_DIR/composer.phar")
elif command -v composer >/dev/null 2>&1; then
  COMPOSER_CMD=("$PHP_BIN" "$(command -v composer)")
else
  info "No composer.phar found and no global composer available. Please install Composer first."
  exit 1
fi

info "Using PHP binary: $PHP_BIN"
info "Using Composer command: ${COMPOSER_CMD[*]}"

info "Removing vendor directory and composer.lock..."
rm -rf "$APP_DIR/vendor"
rm -f "$APP_DIR/composer.lock"

info "Installing dependencies (no-dev, optimized autoloader)..."
"${COMPOSER_CMD[@]}" install --no-dev --optimize-autoloader

info "Clearing Laravel caches..."
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan cache:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear

info "Done. You can now run: $PHP_BIN artisan savarix:diagnose-tenancy-domains --sync"
