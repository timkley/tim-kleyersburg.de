<?php

declare(strict_types=1);

namespace App\Notifications\Holocron\School;

use App\Data\Untis\News;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class NewNews extends Notification
{
    use Queueable;

    public function __construct(public News $news) {}

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        return DiscordMessage::create("{$this->news->subject}: {$this->news->text}");
    }
}
