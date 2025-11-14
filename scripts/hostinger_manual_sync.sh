#!/bin/bash
# Manual Hostinger sync script for Aktonz tenant
#
# This helper exists for the aktonz.savarix.com Hostinger shared hosting account.
# It mirrors /home/$HOST_USER/laravel_app/public into public_html/, rewrites the
# document root's index.php to reference laravel_app/, and refreshes caches.
# Run this directly on the Hostinger server via SSH whenever the GitHub Actions
# deploy job is unavailable and you must refresh the tenant manually.

set -euo pipefail

HOST_USER="u753768407.savarix.com"
APP_DIR="/home/$HOST_USER/laravel_app"
OLD_APP_DIR="/home/$HOST_USER/laravel_app_core"
DOCUMENT_ROOT="/home/$HOST_USER/public_html"
DOMAIN="aktonz.savarix.com"

log() { printf '\n[hostinger-manual] %s\n' "$*"; }

log "== Step 1: Sanity checks =="
ls -lah "/home/$HOST_USER"
if [ ! -d "$APP_DIR" ]; then
  echo "ERROR: $APP_DIR does not exist. Abort." >&2
  exit 1
fi
if [ ! -d "$DOCUMENT_ROOT" ]; then
  echo "ERROR: $DOCUMENT_ROOT does not exist. Abort." >&2
  exit 1
fi

log "== Step 2: Back up laravel_app_core (if present) =="
if [ -d "$OLD_APP_DIR" ]; then
  BACKUP_NAME="${OLD_APP_DIR}_backup_$(date +%Y%m%d_%H%M)"
  echo "Backing up $OLD_APP_DIR -> $BACKUP_NAME"
  mv "$OLD_APP_DIR" "$BACKUP_NAME"
else
  echo "No $OLD_APP_DIR directory found, skipping backup."
fi

log "== Step 3: Sync Git repo (laravel_app is canonical) =="
cd "$APP_DIR"

echo "-- Current git remote(s) --"
git remote -v || echo "WARN: git remote not set (check manually)."

echo "-- Pull latest from remote (if set) --"
git pull --rebase || echo "WARN: git pull failed (maybe no remote)."

echo "-- Commit any local changes (if present) --"
git status
if git diff --quiet && git diff --cached --quiet; then
  echo "No local changes to commit."
else
  git add . || echo "WARN: git add failed (check permissions)."
  git commit -m "server sync: $(date +%Y-%m-%d_%H:%M)" || echo "No local changes to commit."
fi

echo "-- Push back to remote (if set) --"
git push || echo "WARN: git push failed (maybe no remote / auth)."

log "== Step 4: Reset document root contents =="
cd "$DOCUMENT_ROOT"

echo "Removing existing files in $DOCUMENT_ROOT (keep dir)..."
rm -rf ./*

echo "Copying public assets from $APP_DIR/public to $DOCUMENT_ROOT..."
cp -R "$APP_DIR/public/"* "$DOCUMENT_ROOT/"

log "== Step 5: Update index.php include paths =="
INDEX_PHP="$DOCUMENT_ROOT/index.php"
if [ ! -f "$INDEX_PHP" ]; then
  echo "ERROR: $INDEX_PHP not found. Something is wrong with public copy." >&2
  exit 1
fi

sed -i "s|require __DIR__.'/../vendor/autoload.php';|require __DIR__.'/../laravel_app/vendor/autoload.php';|" "$INDEX_PHP"
sed -i "s|\$app = require_once __DIR__.'/../bootstrap/app.php';|\$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';|" "$INDEX_PHP"

log "== Step 6: Permissions =="
chown -R "$HOST_USER":"$HOST_USER" "$APP_DIR" "$DOCUMENT_ROOT" || echo "WARN: chown failed (shared hosting restrictions?)."
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" || echo "WARN: chmod on storage/bootstrap/cache failed."
chmod -R 755 "$DOCUMENT_ROOT" || echo "WARN: chmod on DOCUMENT_ROOT failed."

log "== Step 7: Composer install & artisan optimize =="
cd "$APP_DIR"

composer install --no-dev --optimize-autoloader || echo 'WARN: composer install failed. Check composer path and memory.'

if [ -f ".env" ]; then
  echo "Updating APP_URL in .env to https://$DOMAIN (if key exists)..."
  if grep -q "^APP_URL=" .env; then
    sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" .env
  fi

  echo "Updating TENANT_DOMAIN in .env to $DOMAIN (if key exists)..."
  if grep -q "^TENANT_DOMAIN=" .env; then
    sed -i "s|^TENANT_DOMAIN=.*|TENANT_DOMAIN=$DOMAIN|" .env
  fi
fi

php artisan key:generate --force || echo "WARN: key:generate failed (may already exist)."
php artisan config:cache || echo "WARN: config:cache failed."
php artisan route:cache || echo "WARN: route:cache failed."
php artisan view:cache || echo "WARN: view:cache failed."

log "== Step 8: Reminder – confirm Hostinger document root =="
echo "Ensure the Document Root for $DOMAIN is set to $DOCUMENT_ROOT in hPanel."

echo "== Step 9: Optional cleanup =="
echo "After verifying the site, you can remove any ${OLD_APP_DIR}_backup_* directories manually."

echo
log "Manual Hostinger sync complete."
