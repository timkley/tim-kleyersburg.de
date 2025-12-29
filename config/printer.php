<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Long Polling Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the behavior of the print queue long polling
    | endpoint. The printer client will wait up to the timeout duration
    | for new print jobs to become available.
    |
    */

    'long_poll' => [
        // Maximum time to wait for new items (seconds)
        'timeout' => env('PRINTER_LONG_POLL_TIMEOUT', 30),

        // How often to check for new items during wait (seconds)
        'check_interval' => env('PRINTER_LONG_POLL_CHECK_INTERVAL', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Lock Configuration
    |--------------------------------------------------------------------------
    |
    | The cache lock ensures that only one request processes the print queue
    | at a time, preventing race conditions and duplicate processing.
    |
    */

    'cache_lock' => [
        'key' => 'print-queue',
        'timeout' => 60,
    ],
];
