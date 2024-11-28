<?php

namespace App\Notifications\Holocron\School;

use App\Services\Untis\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class NewExam extends Notification
{
    use Queueable;

    public function __construct(public Exam $exam) {}

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        return DiscordMessage::create("Eine neue Klassenarbeit wurde angekündigt: **{$this->exam->subject}** am {$this->exam->date->format('d.m.Y')}.");
    }
}
