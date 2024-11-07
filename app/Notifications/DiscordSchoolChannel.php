<?php

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;

class DiscordSchoolChannel
{
    use Notifiable;

    public function routeNotificationForDiscord(): string
    {
        return config('services.discord.school_channel');
    }
}
