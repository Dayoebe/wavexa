<?php

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoverabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_expose_complete_discovery_metadata(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<link rel="canonical" href="'.config('app.url').'">', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('name="twitter:card"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('max-image-preview:large', false);
    }

    public function test_filtered_catalogues_are_not_indexed_and_use_the_base_canonical(): void
    {
        $this->get('/radio?q=news')->assertOk()
            ->assertSee('name="robots" content="noindex, follow"', false)
            ->assertSee('<link rel="canonical" href="'.route('radio.index').'">', false);
    }

    public function test_discovery_files_are_stateless_and_machine_readable(): void
    {
        $this->get('/robots.txt')->assertOk()->assertHeaderMissing('set-cookie')
            ->assertSee('Sitemap: '.config('app.url').'/sitemap.xml');

        $sitemap = $this->get('/sitemap.xml')->assertOk()->assertHeaderMissing('set-cookie')
            ->assertSee('/sitemaps/radio.xml', false);
        $this->assertNotFalse(simplexml_load_string($sitemap->getContent()));

        $this->get('/llms.txt')->assertOk()->assertHeaderMissing('set-cookie')->assertSee('Full AI-readable guide');
        $this->get('/llms-full.txt')->assertOk()->assertSee('Source and rights transparency');

        $feed = $this->get('/feed.xml')->assertOk();
        $this->assertNotFalse(simplexml_load_string($feed->getContent()));
    }
}
