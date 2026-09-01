<?php

namespace Tests\Feature\Podcasts;

use App\Enums\MediaType;
use App\Models\Country;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PodcastImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_discovers_podcast_and_synchronizes_playable_episodes(): void
    {
        Country::query()->create(['name' => 'Nigeria', 'iso_alpha_2' => 'NG', 'iso_alpha_3' => 'NGA']);
        Http::fake([
            'itunes.apple.com/search*' => Http::response(['results' => [[
                'collectionId' => 12345, 'collectionName' => 'Lagos Voices', 'artistName' => 'Wavexa Studios',
                'feedUrl' => 'https://podcasts.example.test/feed.xml', 'collectionViewUrl' => 'https://podcasts.apple.com/show/12345',
            ]]]),
            'podcasts.example.test/feed.xml' => Http::response($this->feed(), 200, ['Content-Type' => 'application/rss+xml']),
        ]);

        $this->artisan('wavexa:import-podcasts', ['--term' => 'Nigeria', '--country' => 'NG', '--limit' => 1, '--episodes' => 10])->assertSuccessful();

        $podcast = Media::query()->where('type', MediaType::Podcast)->firstOrFail();
        $episode = Media::query()->where('type', MediaType::PodcastEpisode)->firstOrFail();
        $this->assertSame('Lagos Voices', $podcast->name);
        $this->assertSame('NG', $podcast->country->iso_alpha_2);
        $this->assertSame('Welcome to Lagos', $episode->name);
        $this->assertSame('mp3', $episode->primaryStream->format);
        $this->assertSame('https://cdn.example.test/episode-1.mp3', $episode->primaryStream->url);

        $this->get(route('podcasts.index'))->assertOk()->assertSee('Stories worth hearing.')->assertSee('Lagos Voices');
        $this->get(route('podcasts.show', $podcast->slug))->assertOk()->assertSee('Welcome to Lagos')->assertSee('cdn.example.test/episode-1.mp3');
        $this->get(route('home'))->assertOk()->assertSee(route('podcasts.index'));
    }

    private function feed(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
<channel><title>Lagos Voices</title><description>Stories from Nigeria.</description><link>https://example.test</link><language>en</language><itunes:author>Wavexa Studios</itunes:author><itunes:image href="https://example.test/cover.jpg"/>
<item><title>Welcome to Lagos</title><description>Meet the city.</description><guid>episode-one</guid><pubDate>Mon, 31 Aug 2026 10:00:00 GMT</pubDate><itunes:duration>12:34</itunes:duration><enclosure url="https://cdn.example.test/episode-1.mp3" length="1000" type="audio/mpeg"/></item>
</channel></rss>
XML;
    }
}
