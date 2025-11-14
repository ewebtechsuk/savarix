#!/bin/bash
# Automated Hostinger deployment script tailored for aktonz tenant

set -euo pipefail

log() {
    printf '[deploy] %s\n' "$*"
}

error() {
    printf '[deploy][error] %s\n' "$*" >&2
}

HOST_USER=${HOST_USER:-u753768407.savarix.com}
DOMAIN=${DOMAIN:-aktonz.savarix.com}
APP_ROOT=${APP_ROOT:-/home/${HOST_USER}/laravel_app_core}
ALT_APP_DIR=${ALT_APP_DIR:-/home/${HOST_USER}/laravel_app}
DOCUMENT_ROOT=${DOCUMENT_ROOT:-/home/${HOST_USER}/public_html}
GIT_REPO=${GIT_REPO:-https://github.com/ewebtechsuk/savarix.git}
BRANCH=${BRANCH:-main}

HOME_DIR="/home/${HOST_USER}"

require_dir() {
    local dir="$1"
    if [ ! -d "$dir" ]; then
        error "Required directory not found: $dir"
        exit 1
    fi
}

log "Switching to home directory: ${HOME_DIR}"
cd "${HOME_DIR}" || { error "Unable to change directory to ${HOME_DIR}"; exit 1; }

if [ -d "${ALT_APP_DIR}" ]; then
    backup_dir="${ALT_APP_DIR}_backup_$(date +%Y%m%d_%H%M)"
    log "Backing up alternate application directory to ${backup_dir}"
    mv "${ALT_APP_DIR}" "${backup_dir}"
fi

require_dir "${APP_ROOT}"
cd "${APP_ROOT}"
log "Syncing git repository (${GIT_REPO}) branch ${BRANCH}"

git fetch origin "${BRANCH}"
git reset --hard "origin/${BRANCH}"

require_dir "${DOCUMENT_ROOT}"
cd "${DOCUMENT_ROOT}"
log "Clearing document root ${DOCUMENT_ROOT}"
rm -rf ./*

log "Copying Laravel public assets into document root"
cp -R "${APP_ROOT}/public/". "${DOCUMENT_ROOT}/"

log "Rewriting document root index.php paths"
sed -i "s|require __DIR__.'/../vendor/autoload.php';|require __DIR__.'/../laravel_app_core/vendor/autoload.php';|" "${DOCUMENT_ROOT}/index.php"
sed -i "s|\$app = require_once __DIR__.'/../bootstrap/app.php';|\$app = require_once __DIR__.'/../laravel_app_core/bootstrap/app.php';|" "${DOCUMENT_ROOT}/index.php"

log "Applying permissions"
chown -R "${HOST_USER}:www-data" "${APP_ROOT}" "${DOCUMENT_ROOT}"
chmod -R 775 "${APP_ROOT}/storage" "${APP_ROOT}/bootstrap/cache"
chmod -R 775 "${DOCUMENT_ROOT}"

cd "${APP_ROOT}"
log "Installing composer dependencies"
composer install --no-dev --optimize-autoloader

log "Running Laravel optimization commands"
php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

log "Updating tenant specific environment values"
sed -i "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
sed -i "s|^TENANT_DOMAIN=.*|TENANT_DOMAIN=${DOMAIN}|" .env

log "Committing server sync"
git add .
if git diff --cached --quiet; then
    log "No changes to commit"
else
    git commit -m "server sync: updated on $(date +%Y-%m-%d_%H:%M)"
    git push origin "${BRANCH}"
fi

log "Deployment completed"
echo "⚠️ Please ensure Hostinger document root points to: ${DOCUMENT_ROOT}"
echo "✅ Visit https://${DOMAIN}/login to verify the tenant login page"
