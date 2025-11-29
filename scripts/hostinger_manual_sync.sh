#!/bin/bash
# Manual Hostinger sync script for Aktonz tenant
#
# This helper exists for the aktonz.savarix.com Hostinger shared hosting account.
# It mirrors /home/$HOST_USER/domains/savarix.com/laravel_app/public into
# public_html/, rewrites the document root's index.php to reference
# laravel_app/, and refreshes caches. Run this directly on the Hostinger server
# via SSH whenever the GitHub Actions deploy job is unavailable and you must
# refresh the tenant manually.

set -uo pipefail

HOST_USER="${HOST_USER:-u753768407}"
HOME_DIR="/home/$HOST_USER"
APP_DIR="${APP_DIR:-$HOME_DIR/domains/savarix.com/laravel_app}"
OLD_APP_DIR="${OLD_APP_DIR:-$HOME_DIR/domains/savarix.com/laravel_app_core}"
DOCUMENT_ROOT="${DOCUMENT_ROOT:-$HOME_DIR/domains/savarix.com/public_html}"
DOMAIN="${DOMAIN:-aktonz.savarix.com}"
GIT_REMOTE="${GIT_REMOTE:-origin}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-master}"
PHP_BIN="${PHP_BIN:-/opt/alt/php84/usr/bin/php}"
COMPOSER_BIN="${COMPOSER_BIN:-/usr/local/bin/composer}"

log() {
    printf '\n[hostinger-manual] %s\n' "$*"
}

warn() {
    log "WARN: $*"
}

run_or_warn() {
    if ! "$@"; then
        warn "Command failed (continuing): $*"
        return 1
    fi

    return 0
}

update_env_value() {
    local key="$1"
    local value="$2"
    local file="$3"

    if grep -q "^${key}=" "$file" 2>/dev/null; then
        sed -i "s|^${key}=.*|${key}=${value}|" "$file"
    else
        printf '\n%s=%s\n' "$key" "$value" >>"$file"
    fi
}

log "== Step 1: Sanity checks =="
if [ ! -d "$APP_DIR" ]; then
    echo "ERROR: $APP_DIR does not exist. Abort." >&2
    exit 1
fi
if [ ! -d "$DOCUMENT_ROOT" ]; then
    echo "ERROR: $DOCUMENT_ROOT does not exist. Abort." >&2
    exit 1
fi

log "Using PHP binary: $PHP_BIN"
log "Using Composer binary: $COMPOSER_BIN"

log "== Step 2: Back up laravel_app_core (if present) =="
if [ -d "$OLD_APP_DIR" ]; then
    BACKUP_NAME="${OLD_APP_DIR}_backup_$(date +%Y%m%d_%H%M)"
    log "Backing up $OLD_APP_DIR -> $BACKUP_NAME"
    mv "$OLD_APP_DIR" "$BACKUP_NAME"
else
    log "No $OLD_APP_DIR directory found, skipping backup."
fi

log "== Step 3: Sync Git repo =="
cd "$APP_DIR" || exit 1
if ! git rev-parse --git-dir >/dev/null 2>&1; then
    echo "ERROR: $APP_DIR is not a Git repository." >&2
    exit 1
fi

remote_url=$(git remote get-url "$GIT_REMOTE" 2>/dev/null || true)
if [ -z "$remote_url" ]; then
    warn "No git remote named $GIT_REMOTE configured. Add git@github.com:ewebtechsuk/savarix.git before the next run."
elif [ "$remote_url" != "git@github.com:ewebtechsuk/savarix.git" ]; then
    warn "Git remote $GIT_REMOTE points to $remote_url (expected git@github.com:ewebtechsuk/savarix.git)."
fi

run_or_warn git fetch "$GIT_REMOTE" "$DEPLOY_BRANCH" --prune
run_or_warn git checkout "$DEPLOY_BRANCH"
run_or_warn git pull --ff-only "$GIT_REMOTE" "$DEPLOY_BRANCH"

log "== Step 4: Install Composer dependencies =="
if [ ! -x "$PHP_BIN" ]; then
    warn "Configured PHP binary $PHP_BIN is not executable."
fi

if [ ! -x "$COMPOSER_BIN" ]; then
    warn "Configured Composer binary $COMPOSER_BIN is not executable."
fi

if [ -f scripts/composer-token.php ]; then
    run_or_warn "$PHP_BIN" scripts/composer-token.php
fi
run_or_warn "$PHP_BIN" "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --prefer-dist

log "== Step 5: Update environment values =="
if [ ! -f .env ]; then
    cp .env.example .env
    warn "Created .env from example file. Update secrets if this is the first deploy."
fi

update_env_value APP_ENV production .env
update_env_value APP_URL "https://$DOMAIN" .env
update_env_value TENANT_DOMAIN "$DOMAIN" .env

log "== Step 6: Run artisan tasks =="
run_or_warn "$PHP_BIN" artisan key:generate --force
run_or_warn "$PHP_BIN" artisan migrate --force --no-interaction
run_or_warn "$PHP_BIN" artisan config:cache
run_or_warn "$PHP_BIN" artisan route:cache
run_or_warn "$PHP_BIN" artisan view:cache
run_or_warn "$PHP_BIN" artisan optimize
run_or_warn "$PHP_BIN" artisan savarix:diagnose-tenancy-domains --sync

log "== Step 7: Ensure public_html symlink =="
BASE_DIR="$APP_DIR" HOSTINGER_PUBLIC_HTML="$DOCUMENT_ROOT" bash scripts/hostinger-ensure-public-html-symlink.sh

log "== Step 8: Permissions =="
run_or_warn chown -R "$HOST_USER":"$HOST_USER" "$APP_DIR" "$DOCUMENT_ROOT"
run_or_warn chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
run_or_warn chmod -R 755 "$DOCUMENT_ROOT"

log "== Step 9: Post-deploy reminders =="
log "Document root: $DOCUMENT_ROOT"
log "Laravel app: $APP_DIR"
log "Subdomain should point to $DOCUMENT_ROOT (update in hPanel if needed)."
log "Run \"php artisan tenants:list\" and \"php artisan users:list --tenant=aktonz\" to verify tenants."

log "Manual Hostinger sync complete."
