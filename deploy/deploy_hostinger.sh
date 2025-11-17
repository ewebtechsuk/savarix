#!/usr/bin/env bash
set -e

cd /home/u75788407/laravel_app

# Ensure composer.phar is available
if [ ! -f composer.phar ]; then
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --quiet
  rm composer-setup.php
fi

# Install/update dependencies
php composer.phar install --no-dev --optimize-autoloader

# OPTIONAL: run DB migrations on production (uncomment when comfortable)
# php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Sanity check admin login route & URL
php artisan savarix:check-admin-login
