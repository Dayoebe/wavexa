# Wavexa

Wavexa is a Laravel-based global live-media discovery platform. The application is being developed incrementally; the current codebase contains the Laravel foundation and a versioned API entry point only.

## Requirements

- PHP 8.3 or newer, with the extensions required by Laravel and the selected database driver
- Composer 2
- Node.js and npm
- MySQL 8+ for the normal local environment, or SQLite for lightweight development and tests

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure the `DB_*` variables in `.env`. The included example uses SQLite; create the database file when using that default:

```bash
touch database/database.sqlite
php artisan migrate
php artisan storage:link
npm install
npm run build
```

Alternatively, `composer run setup` performs dependency installation, environment initialization, migrations, and the frontend build after the database configuration is ready.

## Development

Run the Laravel development processes together:

```bash
composer run dev
```

Or run the web and frontend processes independently:

```bash
php artisan serve
npm run dev
```

The public API is versioned under `/api/v1`. Its initial health check is available at:

```text
GET /api/v1/health
```

Route responsibilities are separated as follows:

- `routes/web.php`: public browser routes
- `routes/authenticated.php`: authenticated browser routes
- `routes/admin.php`: future authenticated admin routes
- `routes/api.php`: versioned API routes

Sanctum is the API authentication dependency for future first-party mobile and API token flows. Authentication endpoints are intentionally not part of the current foundation stage.

## Quality checks

```bash
composer test
vendor/bin/pint --test
npm run build
```

Tests use an in-memory SQLite database and do not modify the configured local database.

If the active PHP build does not include `pdo_sqlite`, create a dedicated empty MariaDB database and override the test connection explicitly:

```bash
DB_CONNECTION=mysql DB_DATABASE=wavexa_testing php artisan test
```

Never point `RefreshDatabase` tests at the normal development or production database.

## Initial media architecture

The shared `media` table stores common identity, publication state, website, and geographic references. Type-specific tables extend it for radio stations, TV channels, podcasts, and podcast episodes. Categories, genres, and languages use many-to-many relationships, while artwork, metadata provenance, and playback stream sources remain separate concerns.

Long source identifiers, feed URLs, and stream URLs have SHA-256 companion columns for portable unique constraints. The original values are retained in full. Initial reference data can be loaded idempotently with:

```bash
php artisan db:seed
```

## Environment services

The defaults use database-backed cache, sessions, and queues. Run migrations before serving the application, and process queued work with:

```bash
php artisan queue:work
```

Mail is logged locally by default. Configure `MAIL_*` values only for a real mail provider, and never commit credentials. The local filesystem disk is used by default; `php artisan storage:link` exposes explicitly public files through Laravel's standard public storage link.

## Radio Browser ingestion

Wavexa can import technically healthy directory listings from [Radio Browser](https://www.radio-browser.info/). Imports are idempotent by provider and station UUID and preserve the original metadata, synchronization time, stream health fields, and source URL.

```bash
php artisan wavexa:import-radio --country=NG --limit=100
php artisan wavexa:import-radio --language=English --tag=jazz --limit=50
```

Configuration is available through `RADIO_BROWSER_BASE_URL`, `RADIO_BROWSER_TIMEOUT`, and `RADIO_BROWSER_USER_AGENT`. Keep imports bounded and use queues or scheduled batches when the catalog grows.

The public catalog is available at `/radio`. Playback uses the stream provider URL directly; Wavexa does not proxy or redistribute the audio. Radio Browser health is technical evidence only, and imported records retain a separate pending rights-verification state.
