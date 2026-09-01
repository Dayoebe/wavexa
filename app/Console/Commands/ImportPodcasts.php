<?php

namespace App\Console\Commands;

use App\Services\Podcasts\PodcastImporter;
use Illuminate\Console\Command;

class ImportPodcasts extends Command
{
    protected $signature = 'wavexa:import-podcasts
        {--term=Nigeria : Directory search term}
        {--country=NG : Two-letter storefront and podcast country code}
        {--limit=25 : Maximum podcasts to import}
        {--episodes=25 : Recent episodes to synchronize per podcast}';

    protected $description = 'Discover public podcast RSS feeds and synchronize their recent episodes';

    public function handle(PodcastImporter $importer): int
    {
        $country = strtoupper(trim((string) $this->option('country')));
        if (strlen($country) !== 2) {
            $this->error('The country option must be a two-letter ISO country code.');

            return self::INVALID;
        }

        $this->info('Searching the podcast directory and synchronizing publisher RSS feeds...');
        $result = $importer->searchAndImport(
            trim((string) $this->option('term')),
            $country,
            min(200, max(1, (int) $this->option('limit'))),
            min(100, max(1, (int) $this->option('episodes'))),
        );
        $this->table(['Created', 'Updated', 'Episodes synced', 'Failed'], [array_values($result)]);

        return $result['failed'] > 0 && $result['created'] + $result['updated'] === 0 ? self::FAILURE : self::SUCCESS;
    }
}
