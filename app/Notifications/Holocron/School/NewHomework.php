<?php

declare(strict_types=1);

namespace App\Notifications\Holocron\School;

use App\Data\Untis\Homework;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class NewHomework extends Notification
{
    use Queueable;

    public function __construct(public Homework $homework)
    {
    }

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        return DiscordMessage::create("Es gibt neue Hausaufgaben: {$this->homework->subject}. Fällig am **{$this->homework->dueDate->format('d.m.Y')}**. {$this->homework->text}");
    }
}
