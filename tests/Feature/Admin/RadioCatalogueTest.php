<?php

namespace Tests\Feature\Admin;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Livewire\Admin\Radio\DataQuality;
use App\Livewire\Admin\Radio\Duplicates;
use App\Livewire\Admin\Radio\Form;
use App\Livewire\Admin\Radio\Index;
use App\Models\Category;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Media;
use App\Models\User;
use App\Services\Media\MediaQualityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RadioCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_view_edit_delete_and_restore_a_radio_station(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $country = Country::query()->create(['name' => 'Nigeria', 'iso_alpha_2' => 'NG', 'iso_alpha_3' => 'NGA']);
        $category = Category::query()->create(['name' => 'Music', 'slug' => 'music']);
        $genre = Genre::query()->create(['name' => 'Afrobeats', 'slug' => 'afrobeats']);
        $language = Language::query()->create(['name' => 'English', 'iso_639_1' => 'en', 'iso_639_3' => 'eng']);

        Livewire::test(Form::class)
            ->set('name', 'Lagos Live Radio')
            ->set('slug', 'lagos-live-radio')
            ->set('status', 'published')
            ->set('description', 'Live music and news from Lagos.')
            ->set('websiteUrl', 'https://radio.example.test')
            ->set('countryId', (string) $country->id)
            ->set('callSign', 'LLR')
            ->set('frequency', '101.500')
            ->set('frequencyUnit', 'FM')
            ->set('categoryIds', [$category->id])
            ->set('genreIds', [$genre->id])
            ->set('languageIds', [$language->id])
            ->set('primaryLanguageId', (string) $language->id)
            ->set('artworkUrl', 'https://radio.example.test/logo.png')
            ->set('streams.0.url', 'https://radio.example.test/live.aac')
            ->set('streams.0.format', 'aac')
            ->set('streams.0.codec', 'AAC')
            ->set('streams.0.status', 'online')
            ->set('streams.0.verification_status', 'verified')
            ->call('save')
            ->assertHasNoErrors();

        $station = Media::query()->where('slug', 'lagos-live-radio')->firstOrFail();
        $this->assertSame(MediaType::Radio, $station->type);
        $this->assertSame(MediaStatus::Published, $station->status);
        $this->assertSame('LLR', $station->radioStation->call_sign);
        $this->assertSame('aac', $station->primaryStream->format);
        $this->assertTrue($station->primaryStream->is_primary);
        $this->assertSame([$category->id], $station->categories->modelKeys());
        $this->assertSame([$genre->id], $station->genres->modelKeys());
        $this->assertSame(1, $station->languages->first()->pivot->is_primary);

        $this->get(route('admin.radio.show', $station))->assertOk()->assertSee('Lagos Live Radio')->assertSee('Source provenance')->assertSee('data-play-station', false)->assertSee('data-radio-dock', false);

        Livewire::test(Form::class, ['station' => $station])
            ->set('name', 'Lagos Live FM')
            ->set('callSign', 'LLFM')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('media', ['id' => $station->id, 'name' => 'Lagos Live FM']);
        $this->assertDatabaseHas('radio_stations', ['media_id' => $station->id, 'call_sign' => 'LLFM']);

        Livewire::test(Index::class)->call('delete', $station->id);
        $this->assertSoftDeleted('media', ['id' => $station->id]);

        Livewire::test(Index::class)->call('restore', $station->id);
        $this->assertNotSoftDeleted('media', ['id' => $station->id]);
    }

    public function test_non_admin_cannot_access_radio_management(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.radio.index'))
            ->assertForbidden();
    }

    public function test_admin_can_review_and_merge_an_exact_duplicate_group(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $country = Country::query()->create(['name' => 'Nigeria', 'iso_alpha_2' => 'NG', 'iso_alpha_3' => 'NGA']);
        $first = $this->radio('Wave FM', 'wave-fm', $country->id);
        $duplicate = $this->radio('Wave-FM', 'wave-fm-copy', $country->id);
        $first->streamSources()->create($this->stream('https://radio.example.test/one.mp3', true));
        $duplicate->streamSources()->create($this->stream('https://radio.example.test/two.aac'));
        $signature = app(MediaQualityService::class)->duplicateSignature($first);

        Livewire::test(Duplicates::class)
            ->assertSee('Wave FM')
            ->set('survivors.'.$signature, $first->id)
            ->call('merge', $signature)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('media', ['id' => $duplicate->id]);
        $this->assertSame(2, $first->fresh()->streamSources()->count());
    }

    public function test_data_quality_queue_exposes_missing_radio_metadata(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $station = $this->radio('Incomplete Radio', 'incomplete-radio');

        Livewire::test(DataQuality::class)
            ->assertSee('Incomplete Radio')
            ->assertSee('missing country')
            ->assertSee('missing artwork')
            ->assertSee('missing stream');

        $this->get(route('admin.radio.edit', $station))->assertOk()->assertSee('Edit station');
    }

    private function radio(string $name, string $slug, ?int $countryId = null): Media
    {
        $station = Media::query()->create(['type' => MediaType::Radio, 'status' => MediaStatus::Published, 'name' => $name, 'slug' => $slug, 'country_id' => $countryId]);
        $station->radioStation()->create();

        return $station;
    }

    private function stream(string $url, bool $primary = false): array
    {
        return ['url' => $url, 'url_hash' => hash('sha256', $url), 'protocol' => 'https', 'format' => str_ends_with($url, '.aac') ? 'aac' : 'mp3', 'status' => 'online', 'verification_status' => 'verified', 'is_primary' => $primary];
    }
}
