<?php

namespace App\Services;

use App\Models\Url;
use Illuminate\Support\Str;

class UrlShortenerService
{
    public function shorten(string $originalUrl): Url
    {
        $shortCode = $this->generateUniqueCode();

        return Url::create([
            'original_url' => $originalUrl,
            'short_code'   => $shortCode,
            'visit_count'  => 0,
        ]);
    }

    public function resolve(string $shortCode): ?Url
    {
        return Url::where('short_code', $shortCode)->first();
    }

    public function recordVisit(Url $url): void
    {
        $url->increment('visit_count');
    }

    public function generateCode(int $length = 6): string
    {
        return Str::random($length);
    }

    public function generateUniqueCode(int $length = 6): string
    {
        do {
            $code = $this->generateCode($length);
        } while (Url::where('short_code', $code)->exists());

        return $code;
    }

    public function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public function getStats(string $shortCode): ?array
    {
        $url = $this->resolve($shortCode);

        if (!$url) return null;

        return [
            'short_code'   => $url->short_code,
            'original_url' => $url->original_url,
            'visit_count'  => $url->visit_count,
            'created_at'   => $url->created_at,
        ];
    }
}   