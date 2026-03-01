<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Reminder;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Notifications\QuestReminder;
use NotificationChannels\Discord\DiscordChannel;

it('sends via discord channel', function () {
    $reminder = Reminder::factory()->create();
    $notification = new QuestReminder($reminder);
    $user = User::factory()->create();

    expect($notification->via($user))->toBe([DiscordChannel::class]);
});

it('formats discord message with quest name', function () {
    $reminder = Reminder::factory()->create();
    $reminder->load('quest');
    $notification = new QuestReminder($reminder);
    $user = User::factory()->create();

    $message = $notification->toDiscord($user);

    expect($message->body)->toContain('Erinnerung');
    expect($message->body)->toContain($reminder->quest->name);
});

it('stores the reminder on the notification', function () {
    $reminder = Reminder::factory()->create();
    $notification = new QuestReminder($reminder);

    expect($notification->reminder->is($reminder))->toBeTrue();
});
