<?php

declare(strict_types=1);

namespace App\Notifications\Holocron;

use App\Models\Holocron\Quest\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class QuestReminder extends Notification
{
    use Queueable;

    public function __construct(public Reminder $reminder) {}

    /**
     * @return string[]
     */
    public function via(mixed $notifiable): array
    {
        return [DiscordChannel::class];
    }

    /**
     * Get the Discord representation of the notification.
     */
    public function toDiscord(mixed $notifiable): DiscordMessage
    {
        $quest = $this->reminder->quest;

        $content = "**Reminder**\n";
        $content .= "Quest: **{$quest->name}**\n";

        return DiscordMessage::create($content);
    }

    public function routeNotificationForDiscord(): string
    {
        // Use tim's personal channel for reminders
        return config('services.discord.tim_channel');
    }
}
