#!/usr/bin/env sh

set -eu

echo "[1/4] Verifying PHP runtime"
php --version

echo "[2/4] Verifying Laravel can boot"
php artisan about --only=environment --no-ansi

echo "[3/4] Running production database migrations"
php artisan migrate --force --no-interaction --no-ansi -vvv

echo "[4/4] Building Laravel production caches"
php artisan optimize:clear --no-ansi -v
php artisan optimize --no-ansi -v

echo "Railway pre-deploy completed successfully"
