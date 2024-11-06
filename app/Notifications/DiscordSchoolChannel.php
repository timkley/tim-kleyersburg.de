<?php

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;

class DiscordSchoolChannel
{
    use Notifiable;

    public function routeNotificationForDiscord(): string
    {
        return '1302734860573606058';
    }
}
