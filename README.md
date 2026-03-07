# InventoryManager

InventoryManager is a Laravel + Filament application for managing laboratory inventory, bookings, and loans.

## Tech Stack
- PHP 8.3+
- Laravel 12
- Filament
- MySQL
- Node.js 18+

## Quick Start
```bash
composer install
npm install
cp example.env .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
npm run dev
```

Admin panel: `http://127.0.0.1:8000/admin`

## Useful Commands
```bash
php artisan test
php artisan optimize:clear
npm run build
```

## Notes
- Configure database credentials in `.env` before running migrations.
- Do not commit secrets or your real `.env` values.
