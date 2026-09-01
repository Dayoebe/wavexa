<?php

namespace Tests\Feature\Admin;

use App\Enums\MediaType;
use App\Livewire\Admin\Editorial\MediaCollection;
use App\Livewire\Admin\Editorial\PopularDestinations;
use App\Models\Country;
use App\Models\EditorialPlacement;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditorialDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_curate_and_reorder_featured_media(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $first = Media::factory()->published()->create(['name' => 'First Signal']);
        $second = Media::factory()->published()->create(['name' => 'Second Signal']);

        Livewire::test(MediaCollection::class, ['collection' => 'featured'])
            ->call('add', $first->id)->call('add', $second->id)->assertHasNoErrors();

        $secondPlacement = EditorialPlacement::where('media_id', $second->id)->firstOrFail();
        Livewire::test(MediaCollection::class, ['collection' => 'featured'])->call('move', $secondPlacement->id, 'up');

        $this->assertSame($second->id, EditorialPlacement::where('collection', 'featured')->orderBy('position')->value('media_id'));
    }

    public function test_scheduled_featured_media_and_promoted_destinations_shape_homepage(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $automatic = Country::factory()->create(['name' => 'Automatic Republic']);
        $promoted = Country::factory()->create(['name' => 'Priority Nation']);
        foreach ([[$automatic, 'Automatic Radio'], [$promoted, 'Priority TV']] as [$country, $name]) {
            $media = Media::factory()->published()->create(['name' => $name, 'country_id' => $country->id, 'type' => $name === 'Priority TV' ? MediaType::Television : MediaType::Radio]);
            $media->streamSources()->create(['url' => 'https://example.test/'.$media->id, 'url_hash' => hash('sha256', 'https://example.test/'.$media->id), 'protocol' => 'https', 'format' => 'hls', 'status' => 'online', 'verification_status' => 'verified', 'is_primary' => true]);
        }
        $featured = Media::where('name', 'Priority TV')->firstOrFail();
        EditorialPlacement::create(['media_id' => $featured->id, 'collection' => 'featured', 'position' => 1]);
        Livewire::test(PopularDestinations::class)->call('promote', $promoted->id);

        $response = $this->get(route('home'))->assertOk()->assertSee('Worth discovering now.')->assertSee('Priority TV');
        $this->assertTrue(strpos($response->getContent(), 'Priority Nation') < strpos($response->getContent(), 'Automatic Republic'));
    }

    public function test_editorial_routes_are_available_only_to_admins(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.editorial.trending'))->assertOk()->assertSee('Trending media');
        auth()->logout();
        $this->actingAs(User::factory()->create())->get(route('admin.editorial.featured'))->assertForbidden();
    }
}
