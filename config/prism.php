<?php

declare(strict_types=1);

return [
    'providers' => [
        'openrouter' => [
            'api_key' => env('OPENROUTER_API_KEY', ''),
            'url' => env('OPENROUTER_URL', 'https://openrouter.ai/api/v1'),
        ],
    ],
];
