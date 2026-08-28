<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StreamStatus;
use App\Http\Controllers\Controller;
use App\Models\StreamReport;
use App\Models\StreamSource;
use Illuminate\Contracts\View\View;

class StreamHealthController extends Controller
{
    public function __invoke(): View
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

        return view('admin.stream-health', compact('summary', 'streams'));
    }
}
