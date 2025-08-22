<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\LivewireServiceProvider::class,
    Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
    Modules\Holocron\HolocronServiceProvider::class,
    Modules\Holocron\Bookmarks\BookmarksServiceProvider::class,
    Modules\Holocron\Dashboard\DashboardServiceProvider::class,
    Modules\Holocron\Gear\GearServiceProvider::class,
    Modules\Holocron\Grind\GrindServiceProvider::class,
    Modules\Holocron\Quest\QuestServiceProvider::class,
    Modules\Holocron\School\SchoolServiceProvider::class,
    Modules\Holocron\User\UserServiceProvider::class,
    NotificationChannels\Discord\DiscordServiceProvider::class,
];
