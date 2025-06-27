<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class DiscordTimChannel extends Notification
{
    use Notifiable;

    public function getKey(): string
    {
        return 'discord-tim-channel';
    }

    public function routeNotificationForDiscord(): string
    {
        return config('services.discord.tim_channel');
    }
}
