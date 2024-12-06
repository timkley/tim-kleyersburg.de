<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    NotificationChannels\Discord\DiscordServiceProvider::class,
    Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
];
