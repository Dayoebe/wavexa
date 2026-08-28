<?php

namespace App\Console\Commands;

use App\Services\RadioBrowser\RadioBrowserImporter;
use Illuminate\Console\Command;

class ImportRadioBrowserStations extends Command
{
    protected $signature = 'wavexa:import-radio
        {--country= : ISO 3166-1 alpha-2 country code}
        {--name= : Match a station name}
        {--tag= : Match a genre or tag}
        {--language= : Match a language}
        {--limit=100 : Maximum stations to fetch (1-500)}
        {--offset=0 : Result offset}';

    protected $description = 'Import healthy radio stations from Radio Browser';

    public function handle(RadioBrowserImporter $importer): int
    {
        $limit = max(1, min(500, (int) $this->option('limit')));
        $country = strtoupper(trim((string) $this->option('country')));

        if ($country !== '' && strlen($country) !== 2) {
            $this->error('The country option must be a two-letter ISO country code.');

            return self::INVALID;
        }

        $this->info('Fetching healthy stations from Radio Browser...');
        $result = $importer->import([
            'country' => $country ?: null,
            'name' => $this->option('name'),
            'tag' => $this->option('tag'),
            'language' => $this->option('language'),
            'limit' => $limit,
            'offset' => max(0, (int) $this->option('offset')),
        ]);

        $this->table(['Created', 'Updated', 'Failed'], [[$result['created'], $result['updated'], $result['failed']]]);

        return $result['failed'] > 0 && $result['created'] + $result['updated'] === 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
