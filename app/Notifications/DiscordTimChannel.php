<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;

class DiscordTimChannel
{
    use Notifiable;

    public function routeNotificationForDiscord(): string
    {
        return config('services.discord.tim_channel');
    }
}
