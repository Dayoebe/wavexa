<?php

namespace Tests\Feature\Admin;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Livewire\Admin\Playback\Formats;
use App\Livewire\Admin\Playback\Messages;
use App\Livewire\Admin\Playback\Rights;
use App\Models\Country;
use App\Models\Media;
use App\Models\MediaSource;
use App\Models\PlaybackFormat;
use App\Models\PlaybackMessage;
use App\Models\SourceProvider;
use App\Models\StreamGeoRule;
use App\Models\StreamSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlaybackPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_format_is_blocked_by_public_policy_api(): void
    {
        $stream = $this->stream();
        PlaybackFormat::where('key', 'hls')->update(['is_enabled' => false]);

        $this->getJson(route('api.v1.streams.playback-policy', $stream))->assertOk()
            ->assertJson(['allowed' => false, 'reason' => 'unsupported']);
    }

    public function test_country_rules_are_enforced_from_trusted_country_header(): void
    {
        $stream = $this->stream();
        $country = Country::factory()->create(['iso_alpha_2' => 'NG']);
        StreamGeoRule::create(['stream_source_id' => $stream->id, 'country_id' => $country->id, 'mode' => 'blocked']);

        $this->withHeader('X-Wavexa-Country', 'NG')->getJson(route('api.v1.streams.playback-policy', $stream))->assertJson(['allowed' => false, 'reason' => 'geoblocked']);
        $this->withHeader('X-Wavexa-Country', 'US')->getJson(route('api.v1.streams.playback-policy', $stream))->assertJson(['allowed' => true]);
    }

    public function test_rejected_distribution_rights_block_matching_provider_stream(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $stream = $this->stream();
        $source = MediaSource::where('media_id', $stream->media_id)->firstOrFail();
        Livewire::test(Rights::class)->set("notes.{$source->id}", 'No redistribution permission supplied.')->call('review', $source->id, 'rejected')->assertHasNoErrors();

        $this->getJson(route('api.v1.streams.playback-policy', $stream))->assertJson(['allowed' => false, 'reason' => 'rights_restricted']);
    }

    public function test_admin_can_control_formats_and_playback_messages(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $format = PlaybackFormat::where('key', 'mp3')->firstOrFail();
        Livewire::test(Formats::class)->call('toggle', $format->id)->assertHasNoErrors();
        $this->assertFalse($format->fresh()->is_enabled);

        $message = PlaybackMessage::where('key', 'offline')->firstOrFail();
        Livewire::test(Messages::class)->set("messages.{$message->id}.message", 'This source is taking a short break.')->call('save')->assertHasNoErrors();
        $this->assertSame('This source is taking a short break.', $message->fresh()->message);
    }

    public function test_playback_policy_workspaces_are_admin_only(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.playback.formats'))->assertOk()->assertSee('Stream formats');
        auth()->logout();
        $this->actingAs(User::factory()->create())->get(route('admin.playback.rights'))->assertForbidden();
    }

    private function stream(): StreamSource
    {
        $provider = SourceProvider::create(['name' => 'Test Provider', 'slug' => 'test-provider', 'is_active' => true]);
        $media = Media::create(['type' => MediaType::Television, 'status' => MediaStatus::Published, 'name' => 'Policy TV', 'slug' => 'policy-tv']);
        $media->tvChannel()->create();
        MediaSource::create(['media_id' => $media->id, 'source_provider_id' => $provider->id, 'external_identifier' => 'policy-tv', 'external_identifier_hash' => hash('sha256', 'policy-tv'), 'source_url' => 'https://provider.test/policy-tv', 'imported_at' => now()]);

        return $media->streamSources()->create(['source_provider_id' => $provider->id, 'url' => 'https://stream.test/live.m3u8', 'url_hash' => hash('sha256', 'https://stream.test/live.m3u8'), 'protocol' => 'https', 'format' => 'hls', 'status' => StreamStatus::Online, 'verification_status' => VerificationStatus::Verified, 'is_primary' => true]);
    }
}
