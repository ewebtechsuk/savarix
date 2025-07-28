#!/bin/bash
set -e

# Install PHP dependencies without development packages
composer install --no-dev --optimize-autoloader

# Copy .env.example if .env is missing
if [ ! -f .env ]; then
  cp .env.example .env
fi

# Run database migrations
php artisan migrate --force

# Set writable permissions for storage and cache directories
chmod -R 775 storage bootstrap/cache

echo "Hostinger deployment complete."
