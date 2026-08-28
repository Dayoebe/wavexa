<?php

namespace App\Http\Controllers;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Enums\StreamStatus;
use App\Models\Country;
use App\Models\Media;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class DiscoveryController extends Controller
{
    public function robots(): Response
    {
        $body = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /api/\nDisallow: /login\nDisallow: /register\n\nSitemap: ".Seo::siteUrl('sitemap.xml')."\n";

        return $this->text($body);
    }

    public function sitemap(): Response
    {
        $maps = ['pages', 'countries', 'radio', 'tv'];
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($maps as $map) {
            $xml .= '<sitemap><loc>'.$this->xml(Seo::siteUrl("sitemaps/{$map}.xml")).'</loc></sitemap>';
        }

        return $this->xmlResponse($xml.'</sitemapindex>');
    }

    public function pages(): Response
    {
        return $this->urlset([
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('radio.index'), 'changefreq' => 'hourly', 'priority' => '0.9'],
            ['loc' => route('tv.index'), 'changefreq' => 'hourly', 'priority' => '0.9'],
        ]);
    }

    public function countries(): Response
    {
        $urls = Country::query()->whereHas('media', fn (Builder $query) => $query->where('status', MediaStatus::Published))
            ->orderBy('iso_alpha_2')->get()->map(fn (Country $country) => [
                'loc' => route('countries.show', $country->iso_alpha_2),
                'lastmod' => $country->updated_at?->toAtomString(),
                'changefreq' => 'daily', 'priority' => '0.8',
            ])->all();

        return $this->urlset($urls);
    }

    public function radio(): Response
    {
        return $this->media(MediaType::Radio, 'radio.show');
    }

    public function tv(): Response
    {
        return $this->media(MediaType::Television, 'tv.show');
    }

    public function llms(): Response
    {
        $body = "# Wavexa\n\n> Wavexa is a global discovery directory for live radio and supported live television streams.\n\n## Public resources\n\n- [Home](".route('home').")\n- [Live radio directory](".route('radio.index').")\n- [Live TV directory](".route('tv.index').")\n- [Full AI-readable guide](".Seo::siteUrl('llms-full.txt').")\n- [XML sitemap](".Seo::siteUrl('sitemap.xml').")\n- [Recent media feed](".Seo::siteUrl('feed.xml').")\n\n## Usage notes\n\nMetadata describes third-party stations and channels. Streams are played from their providers; availability and territorial rights may vary.\n";

        return $this->text($body, 'text/markdown; charset=UTF-8');
    }

    public function llmsFull(): Response
    {
        $radio = $this->eligibleMedia(MediaType::Radio)->count();
        $tv = $this->eligibleMedia(MediaType::Television)->count();
        $countries = Country::query()->whereHas('media', fn (Builder $query) => $query->where('status', MediaStatus::Published))->count();
        $recent = Media::query()->where('status', MediaStatus::Published)->whereIn('type', [MediaType::Radio, MediaType::Television])
            ->latest()->limit(30)->get(['type', 'name', 'slug']);
        $items = $recent->map(fn (Media $media) => '- ['.$media->name.']('.route($media->type === MediaType::Radio ? 'radio.show' : 'tv.show', $media->slug).')')->join("\n");
        $body = "# Wavexa: full discovery guide\n\n## Current product\n\nWavexa indexes {$radio} eligible live radio stations and {$tv} eligible live TV channels across {$countries} countries. Podcasts, accounts, recommendations, and mobile apps are planned features and are not represented as currently available.\n\n## Content model\n\nRadio and television detail pages contain names, countries when known, language or genre metadata when supplied, provider attribution, and stream format details. Country pages group currently published radio and TV records.\n\n## Source and rights transparency\n\nRadio metadata is imported from Radio Browser. Television metadata is imported from Free-TV. Wavexa does not claim ownership of third-party media. Streams play directly from listed providers and require separate rights verification for commercial use or redistribution. Stream availability, geo-restrictions, and metadata accuracy can change.\n\n## Canonical directories\n\n- Radio: ".route('radio.index')."\n- Television: ".route('tv.index')."\n- Sitemap: ".Seo::siteUrl('sitemap.xml')."\n- RSS feed: ".Seo::siteUrl('feed.xml')."\n\n## Recently added records\n\n{$items}\n";

        return $this->text($body, 'text/markdown; charset=UTF-8');
    }

    public function ai(): Response
    {
        return $this->text("Wavexa AI discovery guidance\n\nCanonical guide: ".Seo::siteUrl('llms.txt')."\nExpanded guide: ".Seo::siteUrl('llms-full.txt')."\nSitemap: ".Seo::siteUrl('sitemap.xml')."\n");
    }

    public function feed(): Response
    {
        $items = Media::query()->where('status', MediaStatus::Published)->whereIn('type', [MediaType::Radio, MediaType::Television])
            ->latest()->limit(50)->get();
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Wavexa recently added live media</title><link>'.$this->xml(Seo::siteUrl()).'</link><description>Recently added radio stations and television channels on Wavexa.</description>';
        foreach ($items as $item) {
            $url = route($item->type === MediaType::Radio ? 'radio.show' : 'tv.show', $item->slug);
            $xml .= '<item><title>'.$this->xml($item->name).'</title><link>'.$this->xml($url).'</link><guid>'.$this->xml($url).'</guid><pubDate>'.$item->created_at->toRssString().'</pubDate><description>'.$this->xml($item->description ?: 'Live media listed on Wavexa.').'</description></item>';
        }

        return $this->xmlResponse($xml.'</channel></rss>', 'application/rss+xml; charset=UTF-8');
    }

    private function media(MediaType $type, string $route): Response
    {
        $urls = $this->eligibleMedia($type)->orderBy('id')->get(['slug', 'updated_at'])->map(fn (Media $media) => [
            'loc' => route($route, $media->slug), 'lastmod' => $media->updated_at->toAtomString(),
            'changefreq' => 'daily', 'priority' => '0.7',
        ])->all();

        return $this->urlset($urls);
    }

    private function eligibleMedia(MediaType $type): Builder
    {
        return Media::query()->where('type', $type)->where('status', MediaStatus::Published)
            ->whereHas('primaryStream', fn (Builder $query) => $query->where('status', '!=', StreamStatus::Offline));
    }

    /** @param array<int, array<string, string|null>> $urls */
    private function urlset(array $urls): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.$this->xml($url['loc']).'</loc>';
            foreach (['lastmod', 'changefreq', 'priority'] as $key) {
                if (! empty($url[$key])) {
                    $xml .= "<{$key}>".$this->xml($url[$key])."</{$key}>";
                }
            }
            $xml .= '</url>';
        }

        return $this->xmlResponse($xml.'</urlset>');
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function text(string $body, string $type = 'text/plain; charset=UTF-8'): Response
    {
        return response($body)->header('Content-Type', $type)->header('Cache-Control', 'public, max-age=3600');
    }

    private function xmlResponse(string $xml, string $type = 'application/xml; charset=UTF-8'): Response
    {
        return $this->text($xml, $type);
    }
}
