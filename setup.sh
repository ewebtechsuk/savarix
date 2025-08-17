#!/bin/bash

# Install PHP dependencies
composer install

# Install Node.js dependencies (if using frontend assets)
npm install

# Copy .env example if .env does not exist
if [ ! -f .env ]; then
  cp .env.example .env
fi

# Generate Laravel app key
php artisan key:generate

# Run migrations non-interactively (skip confirmation)
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "Setup complete!"