# Wavexa

Wavexa is a mobile-first global live-media discovery platform built with Laravel. It currently provides searchable live radio and television catalogues, geographic discovery, direct provider playback, stream-health monitoring, and source transparency.

Repository: [github.com/Dayoebe/wavexa](https://github.com/Dayoebe/wavexa)

## Features

### Radio

- Imports healthy directory listings from [Radio Browser](https://www.radio-browser.info/)
- Search by station or location and filter by country, region, genre, language, or format
- Popularity, location, name, bitrate, and recent-check sorting
- Trending and recently added discovery
- Direct playback without proxying provider streams
- HLS playback, alternative-source fallback, and detailed playback errors
- Persistent player during internal navigation using Livewire `wire:navigate` and `@persist`
- Provider click tracking and rate-limited broken-stream reports

### Television

- Imports direct streams from [Free-TV IPTV](https://github.com/Free-TV/IPTV)
- Search, country filtering, sorting, country context, and geographic-restriction notices
- HLS playback, alternative-source fallback, and broken-stream reporting
- Provider page URLs such as YouTube, Twitch, and Dailymotion are skipped until dedicated integrations exist

### Discovery and quality

- Popular-country rails and country pages combining radio and TV
- Global type-ahead search across radio, TV, podcasts, countries, genres, and languages, with search-gap analytics
- Admin editorial workspaces for scheduled featured/trending collections, recently added review, and promoted destination ordering
- Active editorial selections appear on the homepage while unpinned destinations retain automatic catalogue-based ranking
- Responsive mobile-app-style interface with collapsible filters
- Incremental country catalogue from provider metadata
- Idempotent imports with provider and external-identifier provenance
- Exact duplicate merging and noisy Radio Browser tag removal
- Language normalization and safe handling of unusually long provider names
- Quality flags for missing artwork, country, streams, and suspicious names

### Stream monitoring

- Bounded queue batches rather than web-request or whole-catalogue checks
- HTTP status, response time, content type, failure reason, and last-success tracking
- Streams become offline after three consecutive failures
- The scheduler queues the 100 stalest checks every five minutes
- Authenticated operations dashboard at `/admin/stream-health`

### Ingestion operations

- Admin workspaces for bounded Radio Browser, Free-TV, and podcast-directory imports
- Imports are dispatched to Laravel queues rather than executed inside web requests
- Source providers can be enabled or disabled without deleting imported catalogue data
- Persistent run history records options, operator, status, result counts, timing, and failures
- Failed or previous runs can be queued again with their original options

### API

- Versioned API foundation under `/api/v1`
- Health endpoint: `GET /api/v1/health`
- Laravel Sanctum installed for future first-party API and mobile authentication

## Technology

- PHP 8.3+, Laravel 13, Livewire 4, and Laravel Sanctum
- MySQL or MariaDB
- Database-backed queues, cache, and sessions by default
- Tailwind CSS 4, Alpine.js, `hls.js`, and Vite 8

## Requirements

- PHP 8.3 or newer with Laravel's required extensions and a database driver
- Composer 2
- MySQL 8+ or a compatible MariaDB version
- Node.js 22+ and npm recommended for the current frontend toolchain
- Git

SQLite can be used when PHP includes `pdo_sqlite`, but MySQL or MariaDB is the primary development configuration.

## Installation

Clone and install dependencies:

```bash
git clone https://github.com/Dayoebe/wavexa.git
cd wavexa
composer install
npm install
```

Create the environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

Create an empty database and update `.env`:

```dotenv
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wavexa
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

Never commit `.env`, credentials, or provider secrets.

Create the schema, seed minimal reference records, and build assets:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
```

Start the application:

```bash
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000).

## Development processes

Run these in separate terminals when actively developing:

```bash
npm run dev
php artisan queue:work
php artisan schedule:work
```

The queue worker processes stream checks. The scheduler dispatches bounded health-check batches. In production, configure Laravel's standard scheduler cron entry and supervise queue workers.

## Importing media

Import globally popular Radio Browser stations in bounded batches:

```bash
php artisan wavexa:import-radio --limit=500 --offset=0
php artisan wavexa:import-radio --limit=500 --offset=500
```

Import a radio selection:

```bash
php artisan wavexa:import-radio --country=NG --limit=100
php artisan wavexa:import-radio --language=English --tag=jazz --limit=50
```

Import television:

```bash
php artisan wavexa:import-tv
php artisan wavexa:import-tv --country=NG --limit=100
```

The global television import inspects up to 5,000 playlist entries by default so the complete current Free-TV catalogue is considered. Country-specific imports can use a smaller limit.

Discover podcasts through Apple Podcasts and synchronize playable episodes from each publisher's public RSS feed:

```bash
php artisan wavexa:import-podcasts --term=Nigeria --country=NG --limit=25 --episodes=20
php artisan wavexa:import-podcasts --term=Technology --country=US --limit=25 --episodes=20
```

Wavexa stores podcast and episode metadata but does not copy or proxy publisher audio. Playback uses the episode enclosure URL supplied by the RSS feed.

Provider settings are configurable in `.env`:

```dotenv
RADIO_BROWSER_BASE_URL=https://de1.api.radio-browser.info
RADIO_BROWSER_TIMEOUT=15
RADIO_BROWSER_USER_AGENT="Wavexa/1.0"

FREE_TV_PLAYLIST_URL=https://raw.githubusercontent.com/Free-TV/IPTV/master/playlist.m3u8
FREE_TV_TIMEOUT=30
APPLE_PODCASTS_BASE_URL=https://itunes.apple.com
APPLE_PODCASTS_TIMEOUT=20
APPLE_PODCASTS_USER_AGENT="Wavexa/1.0"
FREE_TV_USER_AGENT="Wavexa/1.0"
```

Imports are idempotent by provider and external identifier. Never run large imports inside web requests.

## Stream-health monitoring

Queue a bounded check batch:

```bash
php artisan wavexa:check-streams --limit=100
```

Run a few checks synchronously for local diagnosis:

```bash
php artisan wavexa:check-streams --limit=5 --sync
```

The scheduled command requires a running queue worker. Inspect the schedule with:

```bash
php artisan schedule:list
```

The `/admin/stream-health` dashboard is protected by `auth`. Public authentication and admin roles are not implemented yet, so complete authorization before exposing admin access.

## Catalogue cleanup

Normalize metadata, set quality flags, and merge conservative exact duplicates:

```bash
php artisan wavexa:clean-media
```

Normalize without duplicate merging:

```bash
php artisan wavexa:clean-media --no-merge
```

Only exact normalized names sharing the same media type and country merge automatically. Original provider metadata is retained.

## Testing and code quality

Run the full suite:

```bash
php artisan test
```

If PHP does not include `pdo_sqlite`, create a separate empty testing database:

```bash
DB_CONNECTION=mysql DB_DATABASE=wavexa_testing php artisan test
```

Never point `RefreshDatabase` tests at development or production data.

Additional checks:

```bash
vendor/bin/pint --test
php artisan view:cache
npm run build
php artisan route:list
php artisan migrate:status
```

## Routes and project structure

- `routes/web.php`: public discovery, playback, and reporting
- `routes/authenticated.php`: authenticated user routes
- `routes/admin.php`: authenticated operations
- `routes/api.php`: versioned API
- `routes/console.php`: scheduler configuration

Important routes:

```text
GET  /
GET  /radio
GET  /radio/{slug}
GET  /tv
GET  /tv/{slug}
GET  /countries/{code}
POST /streams/{stream}/report
GET  /api/v1/health
GET  /admin/stream-health        authenticated
```

## Playback and rights policy

Wavexa separates media metadata from playback infrastructure. Streams play directly from provider URLs; Wavexa does not proxy, restream, or redistribute media.

A publicly discoverable URL or technical health response does not establish commercial redistribution rights. Imported records retain their source, synchronization metadata, and separate rights-verification status. Production operators must verify the applicable rights and terms.

## Current limitations

- Stream availability and geographic restrictions are provider-controlled.
- Browsers block insecure HTTP streams on HTTPS pages.
- Mobile operating systems can suspend audio when backgrounded or under power-saving restrictions.
- Internal navigation preserves playback. A manual refresh recreates the page, and autoplay policy may require pressing Resume.
- Authentication UI, admin roles, podcasts, favourites, history, recommendations, and the public mobile API remain future stages.

## License and third-party data

Application code follows the repository's license. Imported metadata and streams remain subject to their original providers' licenses, terms, availability, and rights requirements.

## Search engine and AI discovery

Wavexa publishes page metadata, JSON-LD, XML sitemaps, an RSS feed, and factual AI-readable guides:

- `/sitemap.xml`
- `/robots.txt`
- `/feed.xml`
- `/llms.txt`
- `/llms-full.txt`
- `/ai.txt`

Before production deployment, set `APP_URL` to the exact public HTTPS origin, run `php artisan optimize`, and submit `/sitemap.xml` to the relevant webmaster consoles. See [the SEO and AI discoverability report](docs/seo-ai-discoverability-report.md) for eligibility, validation, and launch notes.
