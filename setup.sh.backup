#!/usr/bin/env bash
#
# Laravel project setup script
#
# Usage:
#   ./setup.sh [options]
#
# Options:
#   --production           Run in production mode (composer --no-dev, build assets, optimize caches).
#   --no-dev               Omit Composer dev dependencies (implies prod dependency set only).
#   --skip-migrate         Do not run database migrations.
#   --seed                 Seed the database after migrations.
#   --skip-npm             Skip JS package install/build steps.
#   --optimize             Run artisan optimize + cache builds (also implied by --production).
#   --force-recreate-env   Replace existing .env with example (existing backed up).
#   --env-example=FILE     Alternate env example (default: .env.example).
#   --pm=<npm|yarn|pnpm>   Force JS package manager.
#   --db-wait              Wait for DB host:port to be reachable before migrations.
#   --db-attempts=N        Attempts for DB wait (default: 20).
#   --db-sleep=SECONDS     Sleep between attempts (default: 3).
#   --quiet                Minimal output (disables colored logs).
#   --help                 Show this help header.
#
set -Eeuo pipefail
IFS=$'\n\t'

COLOR_SUPPORT=true
if [[ ! -t 1 ]]; then COLOR_SUPPORT=false; fi
if command -v tput &>/dev/null; then
  if [[ "$(tput colors 2>/dev/null || echo 0)" -lt 8 ]]; then COLOR_SUPPORT=false; fi
fi
[[ -n "${NO_COLOR:-}" ]] && COLOR_SUPPORT=false

if $COLOR_SUPPORT; then
  C_RESET="\033[0m"; C_GREEN="\033[32m"; C_YELLOW="\033[33m"; C_BLUE="\033[34m"; C_RED="\033[31m"
else
  C_RESET=""; C_GREEN=""; C_YELLOW=""; C_BLUE=""; C_RED=""
fi

log() { [[ "${QUIET}" == "true" ]] && return 0; echo -e "${C_BLUE}[setup]${C_RESET} $*"; }
warn() { [[ "${QUIET}" == "true" ]] && return 0; echo -e "${C_YELLOW}[warn]${C_RESET} $*" >&2; }
success() { [[ "${QUIET}" == "true" ]] && return 0; echo -e "${C_GREEN}[ok]${C_RESET} $*"; }
error() { echo -e "${C_RED}[error]${C_RESET} $*" >&2; exit 1; }

PRODUCTION=false
NO_DEV=false
SKIP_MIGRATE=false
DO_SEED=false
SKIP_NPM=false
OPTIMIZE=false
FORCE_RECREATE_ENV=false
ENV_EXAMPLE=".env.example"
FORCED_PM=""
DB_WAIT=false
DB_ATTEMPTS=20
DB_SLEEP=3
QUIET=false

for arg in "$@"; do
  case "$arg" in
    --production) PRODUCTION=true; NO_DEV=true ;; 
    --no-dev) NO_DEV=true ;; 
    --skip-migrate) SKIP_MIGRATE=true ;; 
    --seed) DO_SEED=true ;; 
    --skip-npm) SKIP_NPM=true ;; 
    --optimize) OPTIMIZE=true ;; 
    --force-recreate-env) FORCE_RECREATE_ENV=true ;; 
    --env-example=*) ENV_EXAMPLE="${arg#*=}" ;; 
    --pm=*) FORCED_PM="${arg#*=}" ;; 
    --db-wait) DB_WAIT=true ;; 
    --db-attempts=*) DB_ATTEMPTS="${arg#*=}" ;; 
    --db-sleep=*) DB_SLEEP="${arg#*=}" ;; 
    --quiet) QUIET=true ;; 
    --help) grep '^# ' "$0" | sed 's/^# //'; exit 0 ;; 
    *) error "Unknown argument: $arg (use --help)";;
  esac
done

command -v php >/dev/null || error "php not found in PATH"
command -v composer >/dev/null || error "composer not found in PATH"
[[ -f "$ENV_EXAMPLE" ]] || error "Env example file '$ENV_EXAMPLE' not found"
[[ -f artisan ]] || error "artisan not found (run from project root)"

if [[ -f .env && "$FORCE_RECREATE_ENV" == "true" ]]; then
  ts=$(date +%s)
  warn ".env exists; backing up to .env.bak.$ts"
  cp .env ".env.bak.$ts"
  rm .env
fi

if [[ ! -f .env ]]; then
  log "Creating .env from $ENV_EXAMPLE"
  cp "$ENV_EXAMPLE" .env
else
  log ".env present (not replaced)"
fi

COMPOSER_FLAGS=(install --no-interaction --prefer-dist --no-progress)
[[ "$NO_DEV" == "true" ]] && COMPOSER_FLAGS+=(--no-dev)
log "Composer ${COMPOSER_FLAGS[*]}"
composer "${COMPOSER_FLAGS[@]}"

detect_pm() {
  [[ -n "$FORCED_PM" ]] && { echo "$FORCED_PM"; return; }
  [[ ! -f package.json ]] && { echo ""; return; }
  if command -v pnpm &>/dev/null && [[ -f pnpm-lock.yaml ]]; then echo pnpm; return; fi
  if command -v yarn &>/dev/null && [[ -f yarn.lock ]]; then echo yarn; return; fi
  if command -v npm &>/dev/null; then echo npm; return; fi
  echo ""
}

if [[ -f package.json && "$SKIP_NPM" == "false" ]]; then
  PM=$(detect_pm)
  if [[ -z "$PM" ]]; then
    warn "No JS package manager detected; skipping frontend."
  else
    log "Installing JS deps via $PM"
    if [[ "$PM" == "npm" ]]; then npm install; else $PM install; fi
    if [[ "$PRODUCTION" == "true" ]] && grep -q '"build"' package.json; then
      log "Building production assets"
      if [[ "$PM" == "npm" ]]; then npm run build; else $PM run build; fi
    fi
  fi
else
  log "Skipping JS dependency installation"
fi

need_key() {
  if ! grep -q '^APP_KEY=' .env; then return 0; fi
  local line
  line=$(grep '^APP_KEY=' .env)
  [[ -z "
${line#APP_KEY=}" ]] && return 0
  php artisan tinker --execute='echo empty(config("app.key")) ? 1 : 0;' 2>/dev/null | grep -q '^1$'
}

if need_key; then
  log "Generating APP_KEY"
  php artisan key:generate --force
else
  log "APP_KEY already set"
fi

if [[ "$DB_WAIT" == "true" ]]; then
  export $(grep -E '^(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' .env | sed 's/\r//' || true)
  DB_HOST="${DB_HOST:-localhost}"
  DB_PORT="${DB_PORT:-3306}"
  log "Waiting for DB ${DB_HOST}:${DB_PORT} (attempts=$DB_ATTEMPTS sleep=$DB_SLEEP)"
  attempt=1
  while (( attempt <= DB_ATTEMPTS )); do
    if php -r '\n      $h=getenv("DB_HOST")?: "localhost";\n      $p=(int)(getenv("DB_PORT")?:3306);\n      $t=@fsockopen($h,$p,$e,$s,1.5);\n      if($t){fclose($t);echo "OK";}' | grep -q OK; then
      success "Database reachable"
      break
    fi
    warn "DB not reachable (attempt $attempt/$DB_ATTEMPTS)"
    sleep "$DB_SLEEP"
    ((attempt++))
  done
  (( attempt > DB_ATTEMPTS )) && error "Database unreachable after $DB_ATTEMPTS attempts"
fi

if [[ "$SKIP_MIGRATE" == "false" ]]; then
  log "Running migrations"
  php artisan migrate --force
  if [[ "$DO_SEED" == "true" ]]; then
    log "Seeding database"
    php artisan db:seed --force
  fi
else
  log "Skipping migrations"
fi

if [[ ! -L public/storage ]]; then
  log "Creating storage symlink"
  php artisan storage:link || warn "storage:link failed"
fi

log "Clearing caches"
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

if [[ "$OPTIMIZE" == "true" || "$PRODUCTION" == "true" ]]; then
  log "Building caches (config, route, view)"
  php artisan config:cache || warn "config:cache failed"
  php artisan route:cache || warn "route:cache failed"
  php artisan view:cache || warn "view:cache failed"
  if php artisan list --raw 2>/dev/null | grep -q '^optimize$'; then
    php artisan optimize || warn "optimize failed"
  fi
fi

if [[ -d storage && -d bootstrap/cache ]]; then
  if ! touch storage/framework/.perm_test 2>/dev/null; then
    warn "Permissions may need adjustment: chmod -R ug+rw storage bootstrap/cache"
  else
    rm -f storage/framework/.perm_test
  fi
fi

success "Setup complete"
[[ "$PRODUCTION" == "true" ]] && log "Production mode completed"