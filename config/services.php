<?php

declare(strict_types=1);

return [
    'lastfm' => [
        'user' => 'timmotheus',
        'api_key' => env('LASTFM_API_KEY'),
    ],

    'torchlight' => [
        'api_key' => env('TORCHLIGHT_API_KEY'),
    ],

    'untis' => [
        'user' => env('UNTIS_USER'),
        'password' => env('UNTIS_PASSWORD'),
    ],

    'discord' => [
        'token' => env('DISCORD_BOT_TOKEN'),
        'school_channel' => env('DISCORD_SCHOOL_CHANNEL'),
        'tim_channel' => '1323291795811078205',
    ],

    'firecrawl' => [
        'api_key' => env('FIRECRAWL_API_KEY'),
    ],

    'weatherapi' => [
        'api_key' => env('WEATHERAPI_API_KEY'),
    ],

    'nasa' => [
        'api_key' => env('NASA_API_KEY'),
    ],

    'cloudflare' => [
        'token' => env('CLOUDFLARE_TOKEN'),
        'zone_id' => env('CLOUDFLARE_ZONE_ID'),
    ],
];
