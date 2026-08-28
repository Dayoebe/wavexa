<?php

namespace App\Http\Controllers;

use App\Models\StreamReport;
use App\Models\StreamSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StreamReportController extends Controller
{
    public function store(Request $request, StreamSource $stream): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'in:not_playing,buffering,wrong_content,geoblocked,other'],
            'details' => ['nullable', 'string', 'max:500'],
        ]);
        StreamReport::query()->create([
            'stream_source_id' => $stream->id,
            'reason' => $data['reason'] ?? 'not_playing',
            'details' => $data['details'] ?? null,
            'ip_hash' => hash('sha256', (string) $request->ip().'|'.config('app.key')),
            'user_agent' => mb_strimwidth((string) $request->userAgent(), 0, 500),
        ]);

        return response()->json(['message' => 'Thanks. We will recheck this stream.'], 201);
    }
}
