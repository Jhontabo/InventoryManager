# Production Checklist

## Railway
- Confirm `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `APP_KEY`, and MySQL `DB_*` variables.
- Keep `NIXPACKS_PHP_ROOT_DIR=/app/public` configured.
- Verify the latest deployment is `SUCCESS` and `/up` returns `200`.

## Database
- Run pending migrations with `php artisan migrate --force`.
- Confirm the new performance indexes were applied for `bookings`, `schedules`, and `loans`.
- Seed or verify the demo admin credentials if the environment is public.

## Performance
- Hard refresh the browser after deploys so old Livewire assets do not keep firing `419` requests.
- Check Railway HTTP logs for slow requests on `/admin`, `/admin/login`, and Livewire update endpoints.
- Keep dashboard widgets lazy-loaded and avoid aggressive polling on the free plan.

## Application
- Verify login, dashboard, schedule calendar, readonly booking calendar, and loan approval flows.
- Confirm Filament assets load over `https` and there is no mixed-content warning.
- Run `php artisan optimize:clear` only when troubleshooting stale cache issues.

## Release Checks
- Run `./vendor/bin/pint --test`, `php artisan test`, and `npm run build` before shipping.
- Review `railway.json` when changing startup behavior or healthchecks.
- Keep a rollback point by noting the last healthy Railway deployment ID.
