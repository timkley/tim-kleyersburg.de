<?php

declare(strict_types=1);

namespace App\Notifications\Holocron\School;

use App\Data\Untis\Lesson;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class ClassCancelled extends Notification
{
    use Queueable;

    public function __construct(public Lesson $lesson) {}

    /**
     * @return string[]
     */
    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord(Notification $notifiable): DiscordMessage
    {
        return DiscordMessage::create("Am **{$this->lesson->start->format('d.m.Y')}** um {$this->lesson->start->format('H:i')} fällt {$this->lesson->subject} aus.");
    }
}
