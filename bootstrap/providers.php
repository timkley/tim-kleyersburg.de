<?php

return [
    App\Providers\AppServiceProvider::class,
    NotificationChannels\Discord\DiscordServiceProvider::class,
    \Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
];
