<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    protected $fillable = ['query', 'normalized_query', 'context', 'results_count', 'user_id', 'ip_hash', 'searched_at'];

    protected function casts(): array
    {
        return ['searched_at' => 'datetime'];
    }
}
