<?php

namespace App\Console\Commands;

use App\Jobs\CheckStreamHealth;
use App\Models\StreamSource;
use Illuminate\Console\Command;

class DispatchStreamHealthChecks extends Command
{
    protected $signature = 'wavexa:check-streams {--limit=100 : Maximum checks to queue} {--sync : Run immediately}';

    protected $description = 'Queue a bounded batch of the stalest stream health checks';

    public function handle(): int
    {
        $limit = max(1, min(500, (int) $this->option('limit')));
        $streamIds = StreamSource::query()->orderByRaw('last_checked_at IS NULL DESC')->orderBy('last_checked_at')->limit($limit)->pluck('id');

        foreach ($streamIds as $id) {
            $this->option('sync') ? CheckStreamHealth::dispatchSync($id) : CheckStreamHealth::dispatch($id);
        }

        $this->info("Queued {$streamIds->count()} stream health checks.");

        return self::SUCCESS;
    }
}
