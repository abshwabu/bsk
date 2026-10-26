# Bina Site Kit

A reusable Laravel starter for small-business websites. Public site content is rendered from Blade, and the Filament admin panel is available at `/admin`.

## Requirements

- PHP 8.2 or newer with the SQLite extension for local development
- Composer
- Node.js and npm

## First-time setup

1. Copy `.env.example` to `.env` and set a unique `ADMIN_EMAIL` and strong `ADMIN_PASSWORD`.
2. Run `composer setup`.
3. Start the local server with `php artisan serve`.

The default local database is SQLite at `database/database.sqlite`. Setup creates the database, runs migrations and seeds the configured admin account, links public storage, and builds the frontend assets. Sign in at `http://localhost:8000/admin`.

The example credentials are placeholders. Replace them before using the application.

## Stack and conventions

- Laravel 11, PHP 8.2+, Filament 3, Tailwind CSS 3, Vite, and Pest
- SQLite locally; MySQL is supported by Laravel for deployment
- Models: `App\Models`; Filament resources: `App\Filament\Resources`; page blocks: `App\Blocks`; services: `App\Services`
- Shared helpers live in `app/Support/helpers.php` and are loaded by Composer
- Public content and layout strings belong in Blade templates; client-specific content is configured through the application, not hard-coded in templates
- Sessions and cache use the database; queued work runs synchronously for shared hosting

Run the test suite with `php artisan test` and format PHP with `vendor/bin/pint`.
