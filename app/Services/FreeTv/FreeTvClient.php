<?php

namespace App\Services\FreeTv;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class FreeTvClient
{
    public function playlist(): string
    {
        return $this->request()->get((string) config('services.free_tv.playlist_url'))->throw()->body();
    }

    private function request(): PendingRequest
    {
        return Http::accept('application/vnd.apple.mpegurl')
            ->withUserAgent((string) config('services.free_tv.user_agent'))
            ->timeout((int) config('services.free_tv.timeout'))
            ->retry(2, 500);
    }
}
