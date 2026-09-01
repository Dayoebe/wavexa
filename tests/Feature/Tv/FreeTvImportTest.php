<?php

namespace Tests\Feature\Tv;

use App\Enums\MediaType;
use App\Models\Country;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FreeTvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_imports_direct_channels_and_skips_platform_pages(): void
    {
        Country::query()->create([
            'name' => 'Nigeria', 'iso_alpha_2' => 'NG', 'iso_alpha_3' => 'NGA',
        ]);
        Http::fake([
            'raw.githubusercontent.com/Free-TV/IPTV/master/playlist.m3u8' => Http::response($this->playlist()),
        ]);

        $this->artisan('wavexa:import-tv', ['--country' => 'NG', '--limit' => 10])->assertSuccessful();

        $this->assertDatabaseCount('media', 1);
        $channel = Media::query()->with(['country', 'tvChannel', 'primaryStream', 'sources'])->firstOrFail();
        $this->assertSame(MediaType::Television, $channel->type);
        $this->assertSame('Wavexa News', $channel->name);
        $this->assertSame('NG', $channel->country->iso_alpha_2);
        $this->assertSame('WavexaNews.ng', $channel->tvChannel->call_sign);
        $this->assertSame('hls', $channel->primaryStream->format);
        $this->assertTrue($channel->sources->first()->metadata['is_geoblocked']);
    }

    public function test_tv_catalog_and_player_page_are_public(): void
    {
        Country::query()->create([
            'name' => 'Nigeria', 'iso_alpha_2' => 'NG', 'iso_alpha_3' => 'NGA',
        ]);
        Http::fake([
            'raw.githubusercontent.com/Free-TV/IPTV/master/playlist.m3u8' => Http::response($this->playlist()),
        ]);
        $this->artisan('wavexa:import-tv', ['--country' => 'NG'])->assertSuccessful();
        $channel = Media::query()->firstOrFail();

        $this->get(route('tv.index'))->assertOk()->assertSee('Television without borders.')->assertSee('Wavexa News')->assertSee('Nigeria (1)');
        $this->get(route('tv.show', $channel->slug))->assertOk()
            ->assertSee('Wavexa News')
            ->assertSee('Rights review pending')
            ->assertSee('data-play-tv', false)
            ->assertSee('data-autoplay', false)
            ->assertSee('data-tv-inline-host', false)
            ->assertSee('data-tv-dock', false)
            ->assertSee('z-[45] hidden', false)
            ->assertSee('x-persist="wavexa-tv-player"', false);

        $this->get(route('home'))->assertOk()->assertSee('data-tv-dock', false);
    }

    public function test_command_can_inspect_channels_beyond_the_first_thousand_entries(): void
    {
        $entries = collect(range(1, 1000))->map(fn (int $index): string => <<<M3U
#EXTINF:-1 tvg-name="Platform {$index}" tvg-id="Platform{$index}.us" tvg-country="US" group-title="United States",Platform {$index}
https://www.youtube.com/example/{$index}
M3U)->push(<<<'M3U'
#EXTINF:-1 tvg-name="Itage TV" tvg-id="ItageTV.ng" tvg-country="NG" group-title="Nigeria",Itage TV
https://stream.example.test/itage/playlist.m3u8
M3U)->implode("\n");

        Http::fake([
            'raw.githubusercontent.com/Free-TV/IPTV/master/playlist.m3u8' => Http::response("#EXTM3U\n{$entries}"),
        ]);

        $this->artisan('wavexa:import-tv', ['--limit' => 5000])->assertSuccessful();

        $this->assertDatabaseHas('media', ['name' => 'Itage TV', 'type' => MediaType::Television->value]);
    }

    private function playlist(): string
    {
        return <<<'M3U'
#EXTM3U
#EXTINF:-1 tvg-name="Wavexa News" tvg-logo="https://example.test/logo.png" tvg-id="WavexaNews.ng" tvg-country="NG" group-title="Nigeria",Wavexa News Ⓖ
https://stream.example.test/live/index.m3u8
#EXTINF:-1 tvg-name="Platform Channel" tvg-id="Platform.ng" tvg-country="NG" group-title="Nigeria",Platform Channel Ⓨ
https://www.youtube.com/example/live
M3U;
    }
}
