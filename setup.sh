#!/bin/bash
set -euo pipefail

# Enhanced setup script with flags:
#  --db-wait       Wait for database connectivity before migrations
#  --seed          Run database seeders after migrations (or after refresh)
#  --refresh-db    Run migrate:fresh (drops all tables) then (optionally) seed
#  --skip-migrate  Skip running migrations entirely
#  --skip-npm      Skip installing Node dependencies
#  --optimize      Run Laravel optimize (config/route/view caches)
#  -h|--help       Show help
#
# Examples:
#   ./setup.sh --db-wait --seed
#   ./setup.sh --refresh-db --seed
#   ./setup.sh --skip-npm --skip-migrate
#
# This script is idempotent; safe to re-run.

SEED=false
DB_WAIT=false
REFRESH_DB=false
SKIP_MIGRATE=false
SKIP_NPM=false
OPTIMIZE=false

print_help() {
  sed -n '1,60p' "$0" | grep -E '^#' | sed 's/^# *//'
}

log() { echo -e "[setup] $*"; }
warn() { echo -e "[setup][warn] $*" >&2; }

restore_vendor_cache() {
  # Attempt to hydrate vendor/ from repository snapshots when network access is
  # unavailable.
  if [[ -d deps/vendor ]]; then
    log "Restoring Composer dependencies from deps/vendor cache"
    rm -rf vendor
    mkdir -p vendor
    cp -a deps/vendor/. vendor/
    return 0
  fi

  if [[ -f deps.tar.gz ]]; then
    log "Extracting Composer dependencies from deps.tar.gz cache"
    rm -rf vendor
    tar -xzf deps.tar.gz vendor
    return 0
  fi

  return 1
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --seed) SEED=true ;;
    --db-wait) DB_WAIT=true ;;
    --refresh-db) REFRESH_DB=true ;;
    --skip-migrate) SKIP_MIGRATE=true ;;
    --skip-npm) SKIP_NPM=true ;;
    --optimize) OPTIMIZE=true ;;
    -h|--help) print_help; exit 0 ;;
    *) warn "Unknown argument: $1"; print_help; exit 1 ;;
  esac
  shift
done

# 1. Ensure .env exists
if [[ ! -f .env ]]; then
  log "Creating .env from example"
  cp .env.example .env
fi

# 2. Composer install
if [[ -f composer.json ]]; then
  if [[ -d vendor ]]; then
    log "Composer dependencies (vendor/) already present"
  else
    log "Installing Composer dependencies"
    if composer install --no-interaction --prefer-dist --no-progress; then
      :
    elif restore_vendor_cache; then
      warn "Composer install failed; restored dependencies from local cache"
    else
      warn "Composer install failed and no cached dependencies were available"
      exit 1
    fi
  fi
fi

# 3. Node dependencies (optional)
if [[ "$SKIP_NPM" == "false" && -f package.json ]]; then
  if command -v npm >/dev/null 2>&1; then
    if [[ -f package-lock.json ]]; then
      log "Installing Node dependencies (npm ci)"
      npm ci --no-audit --no-fund
    else
      log "Installing Node dependencies (npm install)"
      npm install --no-audit --no-fund
    fi
    if jq -e '.scripts.build' package.json >/dev/null 2>&1; then
      log "Building frontend assets"
      npm run build
    else
      log "No build script; skipping asset build"
    fi
  else
    warn "npm not available; skipping Node dependency installation"
  fi
fi

# 4. Generate app key if missing
if ! grep -q '^APP_KEY=' .env || grep -q '^APP_KEY=\s*$' .env; then
  log "Generating APP_KEY"
  php artisan key:generate || warn "key:generate failed (app may already have key)"
fi

# 5. Optionally wait for DB
wait_for_db() {
  local host="${DB_HOST:-localhost}" user="${DB_USERNAME:-root}" pass="${DB_PASSWORD:-}" db="${DB_DATABASE:-}" port="${DB_PORT:-3306}" timeout=60
  log "Waiting for MySQL at ${host}:${port} (db='${db}') up to ${timeout}s"
  for i in $(seq 1 $timeout); do
    if mysql -h "$host" -P "$port" -u"$user" -p"$pass" -e "SELECT 1" "$db" >/dev/null 2>&1; then
      log "MySQL is available (after ${i}s)"
      return 0
    fi
    sleep 1
  done
  warn "MySQL not reachable after ${timeout}s; continuing (migrations may fail)"
}

if [[ "$DB_WAIT" == "true" ]]; then
  if command -v mysql >/dev/null 2>&1; then
    wait_for_db
  else
    warn "mysql client missing; cannot --db-wait"
  fi
fi

# 6. Database migrations / refresh
if [[ "$REFRESH_DB" == "true" ]]; then
  log "Refreshing database (migrate:fresh)"
  php artisan migrate:fresh --force
  if [[ "$SEED" == "true" ]]; then
    log "Seeding database"
    php artisan db:seed --force
  fi
  SKIP_MIGRATE=true
fi

if [[ "$SKIP_MIGRATE" == "false" ]]; then
  log "Running migrations"
  php artisan migrate --force || warn "Migrations failed"
  if [[ "$SEED" == "true" ]]; then
    log "Seeding database"
    php artisan db:seed --force || warn "Seeding failed"
  fi
fi

# 7. Cache / optimization
log "Clearing Laravel caches"
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

if [[ "$OPTIMIZE" == "true" ]]; then
  log "Optimizing (config/route/view caches)"
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
fi

log "Setup complete!"