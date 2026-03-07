# Repository Guidelines

## Project Structure & Module Organization

This is a Laravel 12 + Filament 3 application.

- `app/`: core PHP code (`Models/`, `Filament/Resources`, `Http/Controllers`, `Providers/`).
- `routes/`: route definitions (`web.php`, `console.php`).
- `database/`: migrations, seeders, and factories.
- `resources/`: Blade views plus frontend assets in `resources/js` and `resources/css`.
- `public/`: compiled/static assets.
- `tests/Feature`: integration and access-control tests.

## Application Architecture

This is a Laravel application using Filament as the admin panel.

Main concepts:

- Models in `app/Models` represent database entities.
- Filament Resources in `app/Filament/Resources` provide CRUD interfaces.
- Controllers in `app/Http/Controllers` handle custom logic.
- Routes are defined in `routes/web.php`.
- Database schema is managed through migrations in `database/migrations`.

Filament is the primary interface for managing application data.

## Build, Test, and Development Commands

- `composer install`: install PHP dependencies.
- `npm install`: install frontend dependencies.
- `php artisan serve`: run local backend.
- `npm run dev`: run Vite dev server.
- `npm run build`: production frontend build.
- `php artisan migrate` (or `php artisan migrate:refresh --seed`): apply/reset schema.
- `php artisan test`: run test suite.
- `php artisan optimize:clear`: clear cached framework artifacts after config or route changes.

## Coding Style & Naming Conventions

- Follow PSR-12 and Laravel conventions; format with `./vendor/bin/pint`.
- Indentation: 4 spaces for code (see `.READMEditorconfig`), LF line endings.
- Classes: `PascalCase`; methods/variables: `camelCase`; DB columns and migration files: `snake_case`.
- Keep Filament resources under `app/Filament/Resources/*Resource.php`; place page classes in corresponding `Pages/` folders.

## Testing Guidelines

- Framework: PHPUnit (`phpunit.xml`), tests in `tests/Feature` and `tests/Unit` (create `Unit` when adding unit tests).
- Test files must end with `Test.php` (example: `ApplicationAccessTest.php`).
- Prefer feature tests for permission flows, booking lifecycle, and report/export behavior.
- Use targeted runs while developing, e.g. `php artisan test tests/Feature/AdminAccessTest.php`.

## Commit & Pull Request Guidelines

- Use Conventional Commit style seen in history: `feat:`, `fix:`, `refactor:`, `style:`, `docs:`, `ui:`.
- Keep commits focused (one change set per commit) and include migrations/seeders required by the change.
- PRs should include:
  - concise description of behavior changes,
  - linked issue/task,
  - test evidence (`php artisan test` output or scope),
  - screenshots/GIFs for Filament UI updates.

## Security & Configuration Tips

- Never commit secrets; keep `.env` local.
- Validate `APP_ENV`, `APP_DEBUG`, mail, and OAuth settings before deployment.
- For production deploys, use `composer install --no-dev --optimize-autoloader` and `npm run build`.
