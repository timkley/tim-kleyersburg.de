<?php

declare(strict_types=1);

use App\Notifications\DiscordSchoolChannel;

it('routes notification to the configured discord school channel', function () {
    config(['services.discord.school_channel' => 'https://discord.com/api/webhooks/school-test']);

    $channel = new DiscordSchoolChannel;

    expect($channel->routeNotificationForDiscord())->toBe('https://discord.com/api/webhooks/school-test');
});

it('extends the base notification class', function () {
    $channel = new DiscordSchoolChannel;

    expect($channel)->toBeInstanceOf(Illuminate\Notifications\Notification::class);
});
