<section class="min-h-[calc(100vh-5rem)] bg-stone-100 px-4 py-10 sm:py-16">
    <div class="mx-auto grid max-w-5xl overflow-hidden rounded-[28px] border border-stone-200 bg-white shadow-xl shadow-slate-900/5 lg:grid-cols-[0.85fr_1.15fr]">
        <div class="bg-slate-950 p-8 text-white sm:p-12">
            <p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-orange-400">Your Wavexa account</p>
            <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-[-.04em]">Welcome back to the world’s live signals.</h1>
            <p class="mt-5 leading-7 text-slate-400">Sign in to continue to your account and, when authorized, the Wavexa operations dashboard.</p>
            <a wire:navigate href="{{ route('home') }}" class="mt-10 inline-flex text-sm font-bold text-orange-300">← Return to discovery</a>
        </div>
        <div class="p-6 sm:p-12">
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-orange-600">Sign in</p>
            <h2 class="mt-2 text-3xl font-extrabold">Continue to Wavexa</h2>
            <form wire:submit="authenticate" class="mt-8 space-y-5">
                <label class="block"><span class="text-sm font-bold text-slate-700">Email address</span><input wire:model="email" type="email" autocomplete="email" autofocus class="mt-2 min-h-14 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 outline-none focus:border-orange-500" placeholder="you@example.com">@error('email')<span class="mt-2 block text-sm font-semibold text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="block" x-data="{ visible: false }"><span class="text-sm font-bold text-slate-700">Password</span><span class="relative mt-2 block"><input wire:model="password" :type="visible ? 'text' : 'password'" autocomplete="current-password" class="min-h-14 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 pr-14 outline-none focus:border-orange-500" placeholder="Your password"><button type="button" @click="visible = !visible" :aria-label="visible ? 'Hide password' : 'Show password'" :aria-pressed="visible" class="absolute inset-y-0 right-1 grid w-12 place-items-center rounded-xl text-slate-500 hover:bg-stone-200 hover:text-slate-950"><svg x-show="!visible" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg><svg x-cloak x-show="visible" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m3 3 18 18M10.6 6.1A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-2.1 2.8M6.6 6.6C4 8.2 2.5 12 2.5 12s3.5 6 9.5 6a9.5 9.5 0 0 0 3.3-.6M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></span>@error('password')<span class="mt-2 block text-sm font-semibold text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="flex items-center gap-3 text-sm font-semibold text-slate-600"><input wire:model="remember" type="checkbox" class="size-4 rounded border-stone-300 text-orange-600">Keep me signed in</label>
                <button class="flex min-h-14 w-full items-center justify-center rounded-2xl bg-orange-600 px-5 font-extrabold text-white disabled:opacity-60" wire:loading.attr="disabled"><span wire:loading.remove wire:target="authenticate">Sign in</span><span wire:loading wire:target="authenticate">Signing in…</span></button>
            </form>
            <p class="mt-7 text-center text-sm text-slate-500">New to Wavexa? <a wire:navigate href="{{ route('register') }}" class="font-extrabold text-slate-950">Create an account</a></p>
        </div>
    </div>
</section>
