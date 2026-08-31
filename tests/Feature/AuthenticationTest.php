<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_but_does_not_receive_admin_access(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'New Listener')
            ->set('email', 'listener@example.com')
            ->set('password', 'secure-pass')
            ->set('password_confirmation', 'secure-pass')
            ->call('register')
            ->assertRedirect(route('home'));

        $user = User::query()->where('email', 'listener@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->is_admin);
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_sign_in_and_reach_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'super@admin.com',
            'password' => Hash::make('9638'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'super@admin.com')
            ->set('password', '9638')
            ->call('authenticate')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Platform overview');
    }

    public function test_guest_is_redirected_to_login_and_public_navbar_exposes_authentication(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('home'))->assertOk()->assertSee('Sign in')->assertSee('Create account');
        $this->get(route('login'))->assertOk()->assertSee('Continue to Wavexa');
        $this->get(route('register'))->assertOk()->assertSee('Start discovering');
    }
}
