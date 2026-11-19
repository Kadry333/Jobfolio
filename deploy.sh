#!/usr/bin/env bash
# Exit on error
set -e

echo "--- Installing Composer dependencies ---"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "--- Installing Node dependencies ---"
npm ci

echo "--- Building Vite production assets ---"
npm run build

echo "--- Linking storage ---"
php artisan storage:link || true

# Prepare SQLite if used
if [ "${DB_CONNECTION}" = "sqlite" ] || [ -z "${DB_CONNECTION}" ]; then
    mkdir -p database
    touch database/database.sqlite
fi

echo "--- Running database migrations ---"
php artisan migrate --force

echo "--- Caching routes and views ---"
php artisan view:cache
php artisan route:cache

echo "--- Build complete! ---"
