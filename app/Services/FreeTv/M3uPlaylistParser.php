<?php

namespace App\Services\FreeTv;

class M3uPlaylistParser
{
    /** @return array<int, array<string, string>> */
    public function parse(string $playlist): array
    {
        $channels = [];
        $metadata = null;

        foreach (preg_split('/\R/', $playlist) ?: [] as $line) {
            $line = trim($line);

            if (str_starts_with($line, '#EXTINF:')) {
                $metadata = $this->metadata($line);

                continue;
            }

            if ($metadata !== null && $line !== '' && ! str_starts_with($line, '#')) {
                if (filter_var($line, FILTER_VALIDATE_URL)) {
                    $channels[] = [...$metadata, 'url' => $line];
                }

                $metadata = null;
            }
        }

        return $channels;
    }

    /** @return array<string, string> */
    private function metadata(string $line): array
    {
        preg_match_all('/([\w-]+)="([^"]*)"/', $line, $matches, PREG_SET_ORDER);
        $attributes = collect($matches)->mapWithKeys(fn (array $match): array => [$match[1] => $match[2]])->all();
        $displayName = trim((string) preg_replace('/\s*[ⓈⒼⓎⓉ]+\s*/u', ' ', substr($line, strrpos($line, ',') + 1)));
        $name = trim($attributes['tvg-name'] ?? '') ?: $displayName;

        return [
            'id' => trim($attributes['tvg-id'] ?? '') ?: hash('sha256', $name.'|'.($attributes['tvg-country'] ?? '')),
            'name' => $name,
            'display_name' => $displayName,
            'logo' => trim($attributes['tvg-logo'] ?? ''),
            'country_code' => strtoupper(trim($attributes['tvg-country'] ?? '')),
            'group' => trim($attributes['group-title'] ?? ''),
            'channel_number' => trim($attributes['tvg-chno'] ?? ''),
            'is_sd' => str_contains($line, 'Ⓢ') ? '1' : '0',
            'is_geoblocked' => str_contains($line, 'Ⓖ') ? '1' : '0',
        ];
    }
}
