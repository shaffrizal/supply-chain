# Final Submission Checklist

Project: **Supply Chain Intelligence**
Framework: Laravel 12 / PHP 8.2

## Verified

- [x] Composer manifest is valid.
- [x] PHP dependencies have no known security advisories.
- [x] NPM dependencies have no known vulnerabilities.
- [x] All automated tests pass.
- [x] Production frontend assets build successfully.
- [x] All migrations are registered and have run locally.
- [x] Routes compile without duplicates.
- [x] Blade views compile successfully.
- [x] `.env`, API keys, generated reports, uploads, `vendor`, and `node_modules` are excluded from Git.
- [x] Dashboard and realtime overview return HTTP 200.
- [x] External providers use timeouts, cache, and safe fallbacks.
- [x] Admin mutations require authentication and the Admin role.

## Before presenting or deploying

1. Copy `.env.example` to `.env`.
2. Set a unique `APP_KEY` with `php artisan key:generate`.
3. Configure the intended database.
4. Set a strong `ADMIN_PASSWORD`.
5. Add optional API keys where available.
6. Use `APP_ENV=production` and `APP_DEBUG=false` outside local development.
7. Run:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --seed --force
php artisan optimize
```

## Final verification commands

```bash
php artisan test
composer audit
npm audit
php artisan route:list
php artisan schedule:list
```

The academic rubric must still be checked separately because it is not included in this repository.
