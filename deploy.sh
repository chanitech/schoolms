#!/bin/bash
# ─────────────────────────────────────────────────────────────
#  SchoolMS — Shared Hosting Deploy Script
#  Run via SSH:  bash deploy.sh
# ─────────────────────────────────────────────────────────────

set -e

echo "▶ Pulling latest code..."
git pull origin main

echo "▶ Installing PHP dependencies (no dev, optimized)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "▶ Maintenance mode ON..."
php artisan down --retry=60

echo "▶ Running database migrations..."
php artisan migrate --force

echo "▶ Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

echo "▶ Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "▶ Linking storage..."
php artisan storage:link --force 2>/dev/null || true

echo "▶ Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "▶ Maintenance mode OFF..."
php artisan up

echo "✅ Deploy complete!"
