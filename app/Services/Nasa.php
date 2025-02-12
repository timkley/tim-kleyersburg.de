<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Nasa
{
    public static function apod()
    {
        return Http::get('https://api.nasa.gov/planetary/apod', [
            'api_key' => config('services.nasa.api_key'),
        ])->json();
    }
}
