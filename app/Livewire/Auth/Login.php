<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function authenticate(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $key = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        RateLimiter::clear($key);
        session()->regenerate();

        $destination = session()->pull('url.intended');
        if (! $destination) {
            $destination = Auth::user()->is_admin ? route('admin.dashboard') : route('home');
        }

        $this->redirect($destination, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login')->layoutData([
            'title' => 'Sign in — Wavexa',
            'description' => 'Sign in to your Wavexa account.',
            'robots' => 'noindex, nofollow',
        ]);
    }
}
