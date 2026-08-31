<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()->create($validated);

        Auth::login($user);
        session()->regenerate();

        $this->redirectRoute('home', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register')->layoutData([
            'title' => 'Create account — Wavexa',
            'description' => 'Create your Wavexa account.',
            'robots' => 'noindex, nofollow',
        ]);
    }
}
