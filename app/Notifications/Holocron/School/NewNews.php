<?php

declare(strict_types=1);

namespace App\Notifications\Holocron\School;

use App\Data\Untis\News;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Stringable;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class NewNews extends Notification
{
    use Queueable;

    public function __construct(public News $news) {}

    /**
     * @return string[]
     */
    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord(User $notifiable): DiscordMessage
    {
        $text = str($this->news->text)
            ->when($this->news->subject, fn (Stringable $string) => $string->prepend("**{$this->news->subject}:** "))
            ->replace('<br>', "\n");

        return DiscordMessage::create($text->toString());
    }
}
