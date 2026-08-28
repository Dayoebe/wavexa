<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StreamStatus;
use App\Http\Controllers\Controller;
use App\Models\StreamReport;
use App\Models\StreamSource;
use Illuminate\Http\Response;

class StreamHealthController extends Controller
{
    public function __invoke(): Response
    {
        $summary = [
            'total' => StreamSource::query()->count(),
            'healthy' => StreamSource::query()->where('status', StreamStatus::Online)->count(),
            'failing' => StreamSource::query()->where('status', StreamStatus::Offline)->count(),
            'unverified' => StreamSource::query()->whereNull('last_checked_at')->count(),
            'reports' => StreamReport::query()->whereNull('resolved_at')->count(),
        ];
        $streams = StreamSource::query()->with(['media.country', 'reports'])
            ->orderByDesc('failure_count')->orderBy('last_checked_at')->paginate(30);

        return response()
            ->view('admin.stream-health', compact('summary', 'streams'))
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }
}
