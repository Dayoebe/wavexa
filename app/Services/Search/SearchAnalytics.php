<?php

namespace App\Services\Search;

use App\Models\SearchQuery;
use Illuminate\Support\Str;

class SearchAnalytics
{
    public function record(string $query, int $results, string $context = 'global'): void
    {
        $query = Str::squish(Str::limit(strip_tags($query), 100, ''));
        if (mb_strlen($query) < 2) return;
        SearchQuery::query()->create([
            'query' => $query, 'normalized_query' => Str::lower($query), 'context' => $context,
            'results_count' => max(0, $results), 'user_id' => auth()->id(),
            'ip_hash' => request()->ip() ? hash_hmac('sha256', request()->ip(), (string) config('app.key')) : null,
            'searched_at' => now(),
        ]);
    }
}
