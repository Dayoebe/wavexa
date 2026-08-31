<section class="min-h-[calc(100vh-5rem)] bg-stone-100 px-4 py-10 sm:py-16">
    <div class="mx-auto grid max-w-5xl overflow-hidden rounded-[28px] border border-stone-200 bg-white shadow-xl shadow-slate-900/5 lg:grid-cols-[0.85fr_1.15fr]">
        <div class="bg-emerald-950 p-8 text-white sm:p-12">
            <p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-emerald-300">Join Wavexa</p>
            <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-[-.04em]">Build your doorway to live media everywhere.</h1>
            <p class="mt-5 leading-7 text-emerald-100/70">Create an account now. Favorites, history, and personalized discovery will build on this identity in later stages.</p>
            <a wire:navigate href="{{ route('home') }}" class="mt-10 inline-flex text-sm font-bold text-emerald-300">← Explore first</a>
        </div>
        <div class="p-6 sm:p-12">
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-emerald-700">Create account</p>
            <h2 class="mt-2 text-3xl font-extrabold">Start discovering</h2>
            <form wire:submit="register" class="mt-8 space-y-5">
                <label class="block"><span class="text-sm font-bold text-slate-700">Full name</span><input wire:model="name" autocomplete="name" class="mt-2 min-h-14 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 outline-none focus:border-emerald-600">@error('name')<span class="mt-2 block text-sm font-semibold text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-sm font-bold text-slate-700">Email address</span><input wire:model="email" type="email" autocomplete="email" class="mt-2 min-h-14 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 outline-none focus:border-emerald-600">@error('email')<span class="mt-2 block text-sm font-semibold text-rose-600">{{ $message }}</span>@enderror</label>
                <div class="grid gap-5 sm:grid-cols-2">@foreach([['password', 'Password'], ['password_confirmation', 'Confirm password']] as [$field, $label])<label class="block" x-data="{ visible: false }"><span class="text-sm font-bold text-slate-700">{{ $label }}</span><span class="relative mt-2 block"><input wire:model="{{ $field }}" :type="visible ? 'text' : 'password'" autocomplete="new-password" class="min-h-14 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 pr-14 outline-none focus:border-emerald-600"><button type="button" @click="visible = !visible" :aria-label="visible ? 'Hide {{ strtolower($label) }}' : 'Show {{ strtolower($label) }}'" :aria-pressed="visible" class="absolute inset-y-0 right-1 grid w-12 place-items-center rounded-xl text-slate-500 hover:bg-stone-200 hover:text-slate-950"><svg x-show="!visible" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg><svg x-cloak x-show="visible" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m3 3 18 18M10.6 6.1A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-2.1 2.8M6.6 6.6C4 8.2 2.5 12 2.5 12s3.5 6 9.5 6a9.5 9.5 0 0 0 3.3-.6M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></span>@if($field === 'password')@error('password')<span class="mt-2 block text-sm font-semibold text-rose-600">{{ $message }}</span>@enderror@endif</label>@endforeach</div>
                <button class="flex min-h-14 w-full items-center justify-center rounded-2xl bg-emerald-700 px-5 font-extrabold text-white disabled:opacity-60" wire:loading.attr="disabled"><span wire:loading.remove wire:target="register">Create account</span><span wire:loading wire:target="register">Creating account…</span></button>
            </form>
            <p class="mt-7 text-center text-sm text-slate-500">Already registered? <a wire:navigate href="{{ route('login') }}" class="font-extrabold text-slate-950">Sign in</a></p>
        </div>
    </div>
</section>
