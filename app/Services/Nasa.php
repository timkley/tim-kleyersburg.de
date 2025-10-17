<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Nasa
{
    /**
     * @return Collection<int, array<string, mixed>>|null
     */
    public static function apod(): ?Collection
    {
        return Cache::remember('apod', now()->endOfDay(), function () {
            $defaults = collect([
                'url' => null,
                'title' => null,
                'explanation' => null,
            ]);

            try {
                $response = Http::timeout(3)
                    ->get('https://api.nasa.gov/planetary/apod', [
                        'api_key' => config('services.nasa.api_key'),
                    ])
                    ->collect();

                return $defaults->merge($response);
            } catch (ConnectException $e) {
                report($e);

                return null;
            }
        });
    }
}
