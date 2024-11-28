<?php

namespace App\Notifications\Holocron\School;

use App\Services\Untis\Lesson;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class ClassCancelled extends Notification
{
    use Queueable;

    public function __construct(public Lesson $lesson) {}

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        return DiscordMessage::create("Am **{$this->lesson->start->format('d.m.Y')}** um {$this->lesson->start->format('H:i')} fällt {$this->lesson->subject} aus.");
    }
}
