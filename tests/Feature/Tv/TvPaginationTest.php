<?php

namespace Tests\Feature\Tv;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Enums\VerificationStatus;
use App\Livewire\Pages\Tv\Index;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TvPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tv_catalogue_changes_results_with_livewire_pagination(): void
    {
        foreach (range(1, 20) as $number) {
            $name = 'Channel '.str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            $media = Media::query()->create([
                'type' => MediaType::Television,
                'status' => MediaStatus::Published,
                'name' => $name,
                'slug' => str($name)->slug(),
            ]);
            $media->streamSources()->create([
                'url' => "https://stream.example.test/{$number}.m3u8",
                'url_hash' => hash('sha256', "https://stream.example.test/{$number}.m3u8"),
                'protocol' => 'https', 'format' => 'hls',
                'status' => StreamStatus::Online,
                'verification_status' => VerificationStatus::Verified,
                'is_primary' => true,
            ]);
        }

        Livewire::test(Index::class)
            ->assertSee('Channel 01')
            ->assertSee('Showing')
            ->call('nextPage')
            ->assertSee('Channel 19')
            ->assertDontSee('Channel 01')
            ->set('q', 'Channel 03')
            ->assertSet('paginators.page', 1)
            ->assertSee('Channel 03')
            ->assertDontSee('Channel 19');
    }
}
