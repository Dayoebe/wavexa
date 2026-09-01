<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybackMessage extends Model
{
    protected $fillable = ['key', 'label', 'message', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
