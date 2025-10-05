#!/bin/bash
# Laravel deployment script for Hostinger (main branch)

# Exit on error
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

composer_log() { echo "[deploy] $*"; }
composer_warn() { echo "[deploy][warn] $*" >&2; }

source "${SCRIPT_DIR}/scripts/lib/composer.sh"


# 1. SSH into your Hostinger account and navigate to your web root before running this script
# Example: ssh username@your-hostinger-server.com
# cd ~/public_html

# Remove any existing public directory if present (to avoid nested Laravel structure)
if [ -d "public" ] && [ ! -f "artisan" ]; then
    rm -rf public
fi

# 2. Always pull latest changes from main branch
if [ -f "artisan" ]; then
    # Abort any previous merge if there are unmerged files
    if git diff --name-only --diff-filter=U | grep -q '^'; then
        echo "Unmerged files detected. Aborting merge and resetting to remote main branch."
        git merge --abort || true
        git reset --hard origin/main
    fi
    git pull origin main --allow-unrelated-histories --no-rebase
else
    # Clean up directory before cloning if not empty
    if [ "$(ls -A .)" ]; then
        echo "Directory is not empty and no artisan file found. Cleaning up before clone."
        rm -rf ./*
    fi
    git clone -b main https://github.com/ewebtechsuk/ressapp.git .
fi

# 3. (Skip pull if just cloned)

# 4. Install Composer dependencies
COMPOSER_BIN=()
if ! ensure_composer; then
    exit 1
fi

if [ "${COMPOSER_BIN[0]}" = "composer" ]; then
    composer_version=$("${COMPOSER_BIN[@]}" --version | awk '{print $3}' | cut -d. -f1)
    if [ "$composer_version" -lt 2 ]; then
        composer_warn "Composer 2 is required for Laravel 11. Please upgrade Composer using 'composer self-update --2' or Hostinger's control panel."
        exit 1
    fi
fi

"${COMPOSER_BIN[@]}" install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress


# 5. Copy .env if it does not exist
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Copied .env.example to .env. Please edit your .env file with correct settings."
fi

# 6. Generate application key (if not already set)
if ! grep -q '^APP_KEY=' .env || grep -q '^APP_KEY=$' .env; then
    php artisan key:generate
fi

# 7. Run migrations (optional, remove if not needed)
php artisan migrate --force

# 8. Set permissions
chmod -R 775 storage bootstrap/cache

echo "Deployment complete!"
