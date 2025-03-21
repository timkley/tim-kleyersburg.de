<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Nasa
{
    public static function apod(): Collection
    {
        return Cache::remember('apod', now()->endOfDay(), function () {
            return Http::get('https://api.nasa.gov/planetary/apod', [
                'api_key' => config('services.nasa.api_key'),
            ])->collect();
        });
    }
}
