<?php

namespace App\Console\Commands;

use App\Services\Media\MediaQualityService;
use Illuminate\Console\Command;

class CleanMediaCatalog extends Command
{
    protected $signature = 'wavexa:clean-media {--no-merge : Do not merge exact duplicates}';

    protected $description = 'Normalize discovery metadata, flag quality issues, and merge exact duplicates';

    public function handle(MediaQualityService $service): int
    {
        $result = $service->clean(! $this->option('no-merge'));
        $this->table(['Noisy genres removed', 'Media flagged', 'Exact duplicates merged'], [array_values($result)]);

        return self::SUCCESS;
    }
}
