#!/bin/bash

set -euo pipefail

# Remove only the vendor directory to ensure a clean state while keeping composer.lock
rm -rf vendor/

# Clear Composer cache
composer clear-cache

# Reinstall dependencies from the existing lockfile
composer install --no-interaction --prefer-dist --no-progress
