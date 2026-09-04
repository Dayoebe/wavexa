<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Users\Index;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_appoint_and_remove_another_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $listener = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(Index::class)->assertSee($listener->email)->call('setAdmin', $listener->id, true)->assertHasNoErrors();
        $this->assertTrue($listener->fresh()->is_admin);
        Livewire::test(Index::class)->call('setAdmin', $listener->id, false)->assertHasNoErrors();
        $this->assertFalse($listener->fresh()->is_admin);
    }

    public function test_admin_cannot_demote_self_or_leave_platform_without_an_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(Index::class)->call('setAdmin', $admin->id, false)->assertHasErrors('role');
        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_user_management_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.users.index'))->assertOk()->assertSee('All users');
    }
}
