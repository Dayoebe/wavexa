<?php

namespace Tests\Feature\Admin;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Livewire\Admin\Television\DataQuality;
use App\Livewire\Admin\Television\Duplicates;
use App\Livewire\Admin\Television\Form;
use App\Livewire\Admin\Television\Index;
use App\Models\Category;
use App\Models\Country;
use App\Models\Language;
use App\Models\Media;
use App\Models\User;
use App\Services\Media\MediaQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TelevisionCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_view_edit_delete_and_restore_a_tv_channel(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $country = Country::query()->create(['name' => 'Nigeria', 'iso_alpha_2' => 'NG', 'iso_alpha_3' => 'NGA']);
        $category = Category::query()->create(['name' => 'News', 'slug' => 'news']);
        $language = Language::query()->create(['name' => 'English', 'iso_639_1' => 'en', 'iso_639_3' => 'eng']);

        Livewire::test(Form::class)
            ->set('name', 'Lagos News TV')->set('slug', 'lagos-news-tv')->set('status', 'published')
            ->set('description', 'Live television news from Lagos.')->set('websiteUrl', 'https://tv.example.test')
            ->set('countryId', (string) $country->id)->set('callSign', 'LNTV')->set('categoryIds', [$category->id])
            ->set('languageIds', [$language->id])->set('primaryLanguageId', (string) $language->id)
            ->set('artworkUrl', 'https://tv.example.test/logo.png')->set('streams.0.url', 'https://tv.example.test/live.m3u8')
            ->set('streams.0.format', 'hls')->set('streams.0.status', 'online')->set('streams.0.verification_status', 'verified')
            ->call('save')->assertHasNoErrors();

        $channel = Media::query()->where('slug', 'lagos-news-tv')->firstOrFail();
        $this->assertSame(MediaType::Television, $channel->type);
        $this->assertSame(MediaStatus::Published, $channel->status);
        $this->assertSame('LNTV', $channel->tvChannel->call_sign);
        $this->assertSame('hls', $channel->primaryStream->format);
        $this->assertSame([$category->id], $channel->categories->modelKeys());
        $this->get(route('admin.television.index'))->assertOk()->assertSee('Nigeria (1)');
        $this->get(route('admin.television.show', $channel))->assertOk()->assertSee('Lagos News TV')->assertSee('Source provenance')->assertSee('data-play-tv', false)->assertSee('data-tv-inline-host', false)->assertSee('data-tv-dock', false);

        Livewire::test(Form::class, ['channel' => $channel])->set('name', 'Lagos News HD')->set('callSign', 'LNHD')->call('save')->assertHasNoErrors();
        $this->assertDatabaseHas('media', ['id' => $channel->id, 'name' => 'Lagos News HD']);
        $this->assertDatabaseHas('tv_channels', ['media_id' => $channel->id, 'call_sign' => 'LNHD']);

        Livewire::test(Index::class)->call('delete', $channel->id);
        $this->assertSoftDeleted('media', ['id' => $channel->id]);
        Livewire::test(Index::class)->call('restore', $channel->id);
        $this->assertNotSoftDeleted('media', ['id' => $channel->id]);
    }

    public function test_non_admin_cannot_access_tv_management(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.television.index'))->assertForbidden();
    }

    public function test_admin_can_review_and_merge_an_exact_tv_duplicate_group(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $country = Country::query()->create(['name' => 'Nigeria', 'iso_alpha_2' => 'NG', 'iso_alpha_3' => 'NGA']);
        $first = $this->channel('Wave TV', 'wave-tv', $country->id);
        $duplicate = $this->channel('Wave-TV', 'wave-tv-copy', $country->id);
        $first->streamSources()->create($this->stream('https://tv.example.test/one.m3u8', true));
        $duplicate->streamSources()->create($this->stream('https://tv.example.test/two.m3u8'));
        $signature = app(MediaQualityService::class)->duplicateSignature($first);

        Livewire::test(Duplicates::class)->assertSee('Wave TV')->set('survivors.'.$signature, $first->id)->call('merge', $signature)->assertHasNoErrors();
        $this->assertSoftDeleted('media', ['id' => $duplicate->id]);
        $this->assertSame(2, $first->fresh()->streamSources()->count());
    }

    public function test_tv_data_quality_queue_exposes_missing_metadata(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $channel = $this->channel('Incomplete TV', 'incomplete-tv');
        Livewire::test(DataQuality::class)->assertSee('Incomplete TV')->assertSee('missing country')->assertSee('missing artwork')->assertSee('missing stream');
        $this->get(route('admin.television.edit', $channel))->assertOk()->assertSee('Edit channel');
    }

    private function channel(string $name, string $slug, ?int $countryId = null): Media
    {
        $channel = Media::query()->create(['type' => MediaType::Television, 'status' => MediaStatus::Published, 'name' => $name, 'slug' => $slug, 'country_id' => $countryId]);
        $channel->tvChannel()->create();

        return $channel;
    }

    private function stream(string $url, bool $primary = false): array
    {
        return ['url' => $url, 'url_hash' => hash('sha256', $url), 'protocol' => 'https', 'format' => 'hls', 'status' => 'online', 'verification_status' => 'verified', 'is_primary' => $primary];
    }
}
