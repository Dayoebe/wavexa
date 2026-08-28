<?php

namespace App\Support;

use Illuminate\Support\Str;

final class Seo
{
    public static function siteUrl(?string $path = null): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return $path === null ? $base : $base.'/'.ltrim($path, '/');
    }

    public static function canonical(?string $url = null): string
    {
        $url ??= request()->url();

        return strtok($url, '?') ?: self::siteUrl();
    }

    public static function description(string $description): string
    {
        return Str::limit(trim(strip_tags($description)), 160, '');
    }

    public static function image(?string $image = null): string
    {
        if ($image && filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        return self::siteUrl('images/wavexa-social.svg');
    }

    /** @param array<int, array{name: string, url: string}> $breadcrumbs */
    public static function schema(string $title, string $description, string $url, array $breadcrumbs = []): string
    {
        $graph = [
            [
                '@type' => 'Organization',
                '@id' => self::siteUrl('#organization'),
                'name' => 'Wavexa',
                'url' => self::siteUrl(),
                'logo' => self::image(),
            ],
            [
                '@type' => 'WebSite',
                '@id' => self::siteUrl('#website'),
                'name' => 'Wavexa',
                'url' => self::siteUrl(),
                'description' => 'A global discovery directory for live radio and supported live television streams.',
                'publisher' => ['@id' => self::siteUrl('#organization')],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $url.'#webpage',
                'url' => $url,
                'name' => $title,
                'description' => self::description($description),
                'isPartOf' => ['@id' => self::siteUrl('#website')],
            ],
        ];

        if ($breadcrumbs !== []) {
            $graph[] = [
                '@type' => 'BreadcrumbList',
                '@id' => $url.'#breadcrumb',
                'itemListElement' => collect($breadcrumbs)->values()->map(fn (array $item, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ])->all(),
            ];
        }

        return json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
