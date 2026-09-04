@props(['media', 'class' => ''])
@auth
    @php($saved = auth()->user()->favoriteMedia()->whereKey($media->id)->exists())
    <form method="POST" action="{{ route('library.favorites.toggle', $media) }}" class="inline-flex {{ $class }}">@csrf<button class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-stone-300 bg-white px-4 text-sm font-extrabold text-slate-700"><span aria-hidden="true">{{ $saved ? '♥' : '♡' }}</span>{{ $saved ? 'Saved' : 'Save' }}</button></form>
@else
    <a wire:navigate href="{{ route('login', ['save' => $media->id]) }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-stone-300 bg-white px-4 text-sm font-extrabold text-slate-700 {{ $class }}"><span aria-hidden="true">♡</span>Sign in to save</a>
@endauth
