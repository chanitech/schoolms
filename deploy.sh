#!/bin/bash
# ─────────────────────────────────────────────────────────────
#  SchoolMS — Shared Hosting Deploy Script
#  Run via SSH:  bash deploy.sh
# ─────────────────────────────────────────────────────────────

set -e

# The SSH session's default `php` resolves to an older cPanel PHP version
# than this app requires (composer.json needs >=8.3). Running artisan under
# the wrong version fails with exit 255 and no visible error, since
# display_errors is off on this server — it looks like a silent no-op
# instead of a crash. Point explicitly at the ea-php83 binary instead.
PHP_BIN="${PHP_BIN:-/opt/cpanel/ea-php83/root/usr/bin/php}"
if [ ! -x "$PHP_BIN" ]; then
    echo "❌ PHP 8.3 binary not found at $PHP_BIN — set PHP_BIN to the correct path and retry."
    exit 1
fi

echo "▶ Pulling latest code..."
git pull origin main

echo "▶ Installing PHP dependencies (no dev, optimized)..."
"$PHP_BIN" "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction

echo "▶ Maintenance mode ON..."
"$PHP_BIN" artisan down --retry=60

echo "▶ Running database migrations..."
"$PHP_BIN" artisan migrate --force

echo "▶ Clearing caches..."
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear
"$PHP_BIN" artisan event:clear

echo "▶ Rebuilding caches..."
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

echo "▶ Linking storage..."
"$PHP_BIN" artisan storage:link --force 2>/dev/null || true

echo "▶ Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "▶ Maintenance mode OFF..."
"$PHP_BIN" artisan up

echo "✅ Deploy complete!"
