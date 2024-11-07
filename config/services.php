<?php

return [
    'lastfm' => [
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
    ],
];
