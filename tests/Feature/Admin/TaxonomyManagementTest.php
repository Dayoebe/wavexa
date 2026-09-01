<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Taxonomy\TagCleanup;
use App\Livewire\Admin\Taxonomy\Terms;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaxonomyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_categories_genres_and_languages(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Livewire::test(Terms::class, ['kind' => 'categories'])->set('name', 'News')->set('slug', 'news')->set('description', 'News and current affairs.')->call('save')->assertHasNoErrors();
        Livewire::test(Terms::class, ['kind' => 'genres'])->set('name', 'Afrobeats')->set('slug', 'afrobeats')->call('save')->assertHasNoErrors();
        Livewire::test(Terms::class, ['kind' => 'languages'])->set('name', 'Yoruba')->set('iso6391', 'yo')->set('iso6393', 'yor')->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['slug' => 'news']);
        $this->assertDatabaseHas('genres', ['slug' => 'afrobeats']);
        $this->assertDatabaseHas('languages', ['iso_639_3' => 'yor']);
        $this->get(route('admin.taxonomy.categories'))->assertOk()->assertSee('News');
        $this->get(route('admin.taxonomy.genres'))->assertOk()->assertSee('Afrobeats');
        $this->get(route('admin.taxonomy.languages'))->assertOk()->assertSee('Yoruba');
    }

    public function test_merging_terms_preserves_media_assignments_and_primary_language(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $media = Media::factory()->create();
        $source = Genre::query()->create(['name' => 'Hip Hop Music', 'slug' => 'hip-hop-music']);
        $target = Genre::query()->create(['name' => 'Hip-Hop', 'slug' => 'hip-hop']);
        $media->genres()->attach($source);
        Livewire::test(Terms::class, ['kind' => 'genres'])->call('edit', $source->id)->set('mergeTargetId', (string) $target->id)->call('merge')->assertHasNoErrors();
        $this->assertDatabaseMissing('genres', ['id' => $source->id]);
        $this->assertTrue($media->fresh()->genres->contains($target));

        $sourceLanguage = Language::query()->create(['name' => 'English UK', 'iso_639_3' => 'eng']);
        $targetLanguage = Language::query()->create(['name' => 'English', 'iso_639_3' => 'enm']);
        $media->languages()->attach($sourceLanguage, ['is_primary' => true]);
        Livewire::test(Terms::class, ['kind' => 'languages'])->call('edit', $sourceLanguage->id)->set('mergeTargetId', (string) $targetLanguage->id)->call('merge')->assertHasNoErrors();
        $this->assertTrue((bool) $media->fresh()->languages()->whereKey($targetLanguage->id)->first()->pivot->is_primary);
    }

    public function test_tag_cleanup_removes_selected_noisy_genres_only(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $media = Media::factory()->create();
        $noisy = Genre::query()->create(['name' => '101.5 FM', 'slug' => '101-5-fm']);
        $valid = Genre::query()->create(['name' => 'Jazz', 'slug' => 'jazz']);
        $media->genres()->attach([$noisy->id, $valid->id]);

        Livewire::test(TagCleanup::class)->assertSee('101.5 FM')->assertDontSee('Jazz')->set('selected', [$noisy->id])->call('cleanSelected')->assertHasNoErrors();
        $this->assertDatabaseMissing('genres', ['id' => $noisy->id]);
        $this->assertDatabaseHas('genres', ['id' => $valid->id]);
        $this->assertTrue($media->fresh()->genres->contains($valid));
    }

    public function test_admin_can_view_media_assigned_to_each_taxonomy_term(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $station = Media::factory()->create(['name' => 'Lagos Jazz Radio']);
        $category = Category::query()->create(['name' => 'Music', 'slug' => 'music']);
        $genre = Genre::query()->create(['name' => 'Jazz', 'slug' => 'jazz']);
        $language = Language::query()->create(['name' => 'English', 'iso_639_3' => 'eng']);
        $station->categories()->attach($category);
        $station->genres()->attach($genre);
        $station->languages()->attach($language, ['is_primary' => true]);

        foreach (['categories' => $category, 'genres' => $genre, 'languages' => $language] as $kind => $term) {
            $this->get(route('admin.taxonomy.assignments', [$kind, $term->id]))
                ->assertOk()->assertSee('Lagos Jazz Radio')->assertSee('Radio')->assertSee('Manage');
        }
    }

    public function test_non_admin_cannot_access_taxonomy(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.taxonomy.categories'))->assertForbidden();
    }
}
