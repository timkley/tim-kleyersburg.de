<?php

declare(strict_types=1);

use App\Notifications\DiscordTimChannel;

it('returns the correct key', function () {
    $channel = new DiscordTimChannel;

    expect($channel->getKey())->toBe('discord-tim-channel');
});

it('routes notification to the configured discord tim channel', function () {
    config(['services.discord.tim_channel' => 'https://discord.com/api/webhooks/test']);

    $channel = new DiscordTimChannel;

    expect($channel->routeNotificationForDiscord())->toBe('https://discord.com/api/webhooks/test');
});
