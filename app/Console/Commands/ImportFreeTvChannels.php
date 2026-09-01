<?php

namespace App\Console\Commands;

use App\Services\FreeTv\FreeTvImporter;
use Illuminate\Console\Command;

class ImportFreeTvChannels extends Command
{
    protected $signature = 'wavexa:import-tv {--country= : ISO 3166-1 alpha-2 country code} {--limit=5000 : Maximum playlist entries to inspect}';

    protected $description = 'Import direct television streams from the Free-TV playlist';

    public function handle(FreeTvImporter $importer): int
    {
        $country = strtoupper(trim((string) $this->option('country')));

        if ($country !== '' && strlen($country) !== 2) {
            $this->error('The country option must be a two-letter ISO country code.');

            return self::INVALID;
        }

        $this->info('Fetching direct streams from Free-TV...');
        $result = $importer->import($country ?: null, max(1, min(5000, (int) $this->option('limit'))));
        $this->table(['Created', 'Updated', 'Skipped', 'Failed'], [array_values($result)]);

        return $result['failed'] > 0 && $result['created'] + $result['updated'] === 0 ? self::FAILURE : self::SUCCESS;
    }
}
