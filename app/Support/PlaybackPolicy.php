<?php

namespace App\Support;

use App\Models\PlaybackFormat;
use App\Models\PlaybackMessage;
use App\Models\StreamSource;
use Illuminate\Http\Request;

class PlaybackPolicy
{
    /** @return array{allowed: bool, reason: ?string, message: ?string} */
    public static function evaluate(StreamSource $stream, Request $request): array
    {
        $format = PlaybackFormat::query()->where('key', strtolower($stream->format))->first();
        if ($format && ! $format->is_enabled) {
            return self::denied('unsupported');
        }

        $rightsRejected = $stream->media?->sources()->where('source_provider_id', $stream->source_provider_id)
            ->whereHas('rightsReview', fn ($query) => $query->where('status', 'rejected'))->exists();
        if ($rightsRejected) {
            return self::denied('rights_restricted');
        }

        $countryCode = strtoupper(trim((string) ($request->header('CF-IPCountry') ?: $request->header('X-Wavexa-Country'))));
        if (strlen($countryCode) === 2) {
            $rules = $stream->geoRules()->with('country')->get();
            $blocked = $rules->where('mode', 'blocked')->contains(fn ($rule) => $rule->country?->iso_alpha_2 === $countryCode);
            $allowedRules = $rules->where('mode', 'allowed');
            $outsideAllowlist = $allowedRules->isNotEmpty() && ! $allowedRules->contains(fn ($rule) => $rule->country?->iso_alpha_2 === $countryCode);
            if ($blocked || $outsideAllowlist) {
                return self::denied('geoblocked');
            }
        }

        return ['allowed' => true, 'reason' => null, 'message' => null];
    }

    /** @return array{allowed: false, reason: string, message: string} */
    private static function denied(string $reason): array
    {
        $fallback = $reason === 'geoblocked' ? 'This stream is not available in your location.' : 'This stream is unavailable under the current playback policy.';
        $message = PlaybackMessage::query()->where('key', $reason)->where('is_active', true)->value('message') ?: $fallback;

        return ['allowed' => false, 'reason' => $reason, 'message' => $message];
    }
}
