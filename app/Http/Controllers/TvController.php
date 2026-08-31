<?php

namespace App\Http\Controllers;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Media;
use Illuminate\Contracts\View\View;

class TvController extends Controller
{
    public function show(string $slug): View
    {
        $channel = Media::query()->where('type', MediaType::Television)
            ->where('status', MediaStatus::Published)->where('slug', $slug)
            ->with(['country', 'tvChannel', 'artworks', 'primaryStream.sourceProvider', 'streamSources', 'sources.sourceProvider'])
            ->firstOrFail();

        return view('tv.show', compact('channel'));
    }
}
