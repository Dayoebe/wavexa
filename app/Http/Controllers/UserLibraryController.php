<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\StreamSource;
use App\Models\UserPlaybackHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserLibraryController extends Controller
{
    public function toggleFavorite(Request $request, Media $media): RedirectResponse|JsonResponse
    {
        $result = $request->user()->favoriteMedia()->toggle($media->id);
        $saved = in_array($media->id, $result['attached'], true);

        return $request->expectsJson()
            ? response()->json(['saved' => $saved])
            : back()->with('status', $saved ? 'Saved to your library.' : 'Removed from your library.');
    }

    public function recordPlayback(Request $request, Media $media): JsonResponse
    {
        $history = UserPlaybackHistory::query()->firstOrCreate(
            ['user_id' => $request->user()->id, 'media_id' => $media->id],
            ['last_played_at' => now(), 'play_count' => 0],
        );
        $history->increment('play_count', 1, ['last_played_at' => now()]);

        return response()->json(['recorded' => true]);
    }

    public function recordStreamPlayback(Request $request, StreamSource $stream): JsonResponse
    {
        return $this->recordPlayback($request, $stream->media);
    }
}
