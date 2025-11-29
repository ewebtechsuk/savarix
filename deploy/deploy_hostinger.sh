#!/usr/bin/env bash
set -euo pipefail

info() {
  printf "[deploy_hostinger] %s\n" "$1"
}

warn() {
  printf "[deploy_hostinger][warn] %s\n" "$1" >&2
}

abort() {
  printf "[deploy_hostinger][error] %s\n" "$1" >&2
  exit 1
}

HOSTINGER_USER=${HOSTINGER_USER:-u753768407}
HOSTINGER_DOMAIN_ROOT=${HOSTINGER_DOMAIN_ROOT:-/home/${HOSTINGER_USER}/domains/savarix.com}
APP_DIR=${HOSTINGER_APP_DIR:-${HOSTINGER_DOMAIN_ROOT}/laravel_app}
PUBLIC_HTML=${HOSTINGER_PUBLIC_HTML:-${HOSTINGER_DOMAIN_ROOT}/public_html}
PHP_BIN=${PHP_BIN:-/opt/alt/php83/usr/bin/php}
COMPOSER_BIN=${COMPOSER_BIN:-${APP_DIR}/composer.phar}

export PATH=/opt/alt/php83/usr/bin:"$PATH"

info "App directory: ${APP_DIR}"
info "Public HTML: ${PUBLIC_HTML}"
info "PHP binary: ${PHP_BIN}"

if [[ ! -x "$PHP_BIN" ]]; then
  abort "PHP binary ${PHP_BIN} is not executable. Configure PHP_BIN before deploying."
fi

php_minor_version=$($PHP_BIN -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
required_version=8.3
if [[ $(printf '%s\n' "$required_version" "$php_minor_version" | sort -V | head -n1) != "$required_version" ]]; then
  warn "PHP version ${php_minor_version} detected. Hostinger should be pinned to ${required_version} or newer."
fi

cd "$APP_DIR" || abort "Cannot change into ${APP_DIR}. Does the repository exist on the server?"

if [[ ! -f "$COMPOSER_BIN" ]]; then
  info "composer.phar not found; downloading locally."
  $PHP_BIN -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  $PHP_BIN composer-setup.php --quiet || abort "Composer installer failed"
  rm composer-setup.php
  mv composer.phar "$COMPOSER_BIN"
fi

info "Installing Composer dependencies (no-dev)"
if ! $PHP_BIN "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --prefer-dist; then
  abort "Composer install failed. Check PHP version and memory limits on Hostinger."
fi

if [[ ! -f .env ]]; then
  warn ".env is missing. Copying from example for safety."
  cp .env.example .env
fi

if [[ ! -f .env ]]; then
  abort "Unable to proceed without a .env file."
fi

if [[ ! -f public/build/manifest.json ]]; then
  warn "public/build/manifest.json is missing. Ensure npm run build ran before rsync."
fi

info "Clearing caches"
$PHP_BIN artisan config:clear
$PHP_BIN artisan route:clear
$PHP_BIN artisan view:clear

info "Running migrations"
$PHP_BIN artisan migrate --force --no-interaction

info "Rebuilding caches"
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

info "Syncing tenancy domains"
$PHP_BIN artisan savarix:diagnose-tenancy-domains --sync

info "Ensuring public_html symlink points at the Laravel public directory"
BASE_DIR="$APP_DIR" bash scripts/hostinger-ensure-public-html-symlink.sh

info "Sanity check admin login route & URL"
$PHP_BIN artisan savarix:check-admin-login
