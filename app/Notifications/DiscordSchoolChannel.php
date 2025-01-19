<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class DiscordSchoolChannel extends Notification
{
    use Notifiable;

    public function routeNotificationForDiscord(): string
    {
        return config('services.discord.school_channel');
    }
}
