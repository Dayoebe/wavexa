<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Stream health — Wavexa</title>@fonts @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-50 text-slate-950">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-widest text-orange-600">Wavexa operations</p>
                <h1 class="mt-2 text-3xl font-extrabold">Stream health</h1>
            </div><a href="{{ route('home') }}" class="rounded-xl bg-white px-4 py-2 text-sm font-bold shadow-sm">Public
                site</a>
        </div>
        <div class="mt-8 grid grid-cols-2 gap-3 lg:grid-cols-5">
            @foreach ($summary as $label => $value)
                <div class="rounded-2xl bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold capitalize text-slate-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-extrabold">{{ number_format($value) }}</p>
                </div>
            @endforeach
        </div>
        <div class="mt-8 overflow-hidden rounded-2xl border border-stone-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-stone-100 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="p-4">Media</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Response</th>
                            <th class="p-4">Failures</th>
                            <th class="p-4">Last check</th>
                            <th class="p-4">Reports</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($streams as $stream)
                            <tr>
                                <td class="p-4 font-bold">{{ $stream->media?->name }}<span
                                        class="block text-xs font-normal text-slate-400">{{ $stream->media?->country?->name }}</span>
                                </td>
                                <td class="p-4">{{ $stream->status->value }}</td>
                                <td class="p-4">{{ $stream->http_status ?? '—' }} ·
                                    {{ $stream->response_time_ms ? $stream->response_time_ms . 'ms' : '—' }}</td>
                                <td class="p-4">{{ $stream->failure_count }}</td>
                                <td class="p-4">{{ $stream->last_checked_at?->diffForHumans() ?? 'Never' }}</td>
                                <td class="p-4">{{ $stream->reports->whereNull('resolved_at')->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $streams->links() }}</div>
    </main>
</body>
</html>