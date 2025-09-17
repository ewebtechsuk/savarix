#!/bin/bash

# Remove the vendor directory to ensure a clean state while keeping the lockfile
rm -rf vendor

# Clear Composer cache
composer clear-cache

# Run the existing setup script to reinstall dependencies via the lockfile
./setup.sh
