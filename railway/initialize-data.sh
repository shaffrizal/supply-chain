#!/usr/bin/env sh

set -eu

php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction
php artisan worldbank:sync --fresh --no-interaction
php artisan risk:update --no-interaction
