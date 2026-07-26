#!/usr/bin/env sh

set -eu

php artisan migrate --force --no-interaction
php artisan optimize:clear
php artisan optimize
