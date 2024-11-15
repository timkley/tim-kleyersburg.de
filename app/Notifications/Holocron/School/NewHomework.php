<?php

namespace App\Notifications\Holocron\School;

use App\Services\Untis\Homework;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class NewHomework extends Notification
{
    use Queueable;

    public function __construct(public Homework $homework)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        return DiscordMessage::create("Es gibt neue Hausaufgaben: {$this->homework->subject}");
    }
}
