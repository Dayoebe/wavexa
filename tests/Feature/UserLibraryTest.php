<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\Media;
use App\Models\User;
use App\Models\UserPlaybackHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_requires_authentication(): void
    {
        $this->get(route('library'))->assertRedirect(route('login'));
    }

    public function test_user_can_save_and_remove_media_from_private_library(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $media = Media::factory()->published()->create();

        $this->actingAs($user)->postJson(route('library.favorites.toggle', $media))->assertOk()->assertJson(['saved' => true]);
        $this->assertDatabaseHas('user_favorites', ['user_id' => $user->id, 'media_id' => $media->id]);
        $this->actingAs($other)->get(route('library'))->assertOk()->assertDontSee($media->name);
        $this->actingAs($user)->postJson(route('library.favorites.toggle', $media))->assertOk()->assertJson(['saved' => false]);
        $this->assertDatabaseMissing('user_favorites', ['user_id' => $user->id, 'media_id' => $media->id]);
    }

    public function test_playback_history_is_counted_and_private(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $media = Media::factory()->published()->create(['name' => 'Private Listening Choice']);

        $this->actingAs($user)->postJson(route('library.history.record', $media))->assertOk();
        $this->actingAs($user)->postJson(route('library.history.record', $media))->assertOk();
        $this->assertSame(2, UserPlaybackHistory::whereBelongsTo($user)->where('media_id', $media->id)->value('play_count'));
        $this->actingAs($user)->get(route('library', ['tab' => 'history']))->assertSee('Private Listening Choice');
        $this->actingAs($other)->get(route('library', ['tab' => 'history']))->assertDontSee('Private Listening Choice');
    }

    public function test_guest_save_intent_is_completed_after_login(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);
        $media = Media::factory()->published()->create();

        Livewire::withQueryParams(['save' => $media->id])->test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->call('authenticate')
            ->assertRedirect(route('library'));

        $this->assertDatabaseHas('user_favorites', ['user_id' => $user->id, 'media_id' => $media->id]);
    }
}
