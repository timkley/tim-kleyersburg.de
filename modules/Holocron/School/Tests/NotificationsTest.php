<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Modules\Holocron\School\Data\Exam;
use Modules\Holocron\School\Data\Homework;
use Modules\Holocron\School\Data\Lesson;
use Modules\Holocron\School\Data\News;
use Modules\Holocron\School\Notifications\ClassCancelled;
use Modules\Holocron\School\Notifications\NewExam;
use Modules\Holocron\School\Notifications\NewHomework;
use Modules\Holocron\School\Notifications\NewNews;
use NotificationChannels\Discord\DiscordChannel;

// ClassCancelled notification
it('creates a ClassCancelled notification with a lesson', function () {
    $lesson = Lesson::create(
        id: 1,
        subject: 'Math',
        start: Carbon::parse('2026-03-10 08:00'),
        end: Carbon::parse('2026-03-10 08:45'),
        cancelled: true,
    );

    $notification = new ClassCancelled($lesson);

    expect($notification->lesson)->toBe($lesson)
        ->and($notification->lesson->subject)->toBe('Math');
});

it('sends ClassCancelled via Discord channel', function () {
    $lesson = Lesson::create(
        id: 1,
        subject: 'Math',
        start: Carbon::parse('2026-03-10 08:00'),
        end: Carbon::parse('2026-03-10 08:45'),
        cancelled: true,
    );

    $notification = new ClassCancelled($lesson);

    expect($notification->via(new stdClass))->toBe([DiscordChannel::class]);
});

it('formats ClassCancelled Discord message correctly', function () {
    $lesson = Lesson::create(
        id: 1,
        subject: 'Deutsch',
        start: Carbon::parse('2026-03-10 09:30'),
        end: Carbon::parse('2026-03-10 10:15'),
        cancelled: true,
    );

    $notification = new ClassCancelled($lesson);
    $message = $notification->toDiscord($notification);

    expect($message->body)->toContain('10.03.2026')
        ->and($message->body)->toContain('09:30')
        ->and($message->body)->toContain('Deutsch');
});

// NewExam notification
it('creates a NewExam notification with an exam', function () {
    $exam = Exam::create(
        id: 1,
        subject: 'Physics',
        date: CarbonImmutable::parse('2026-03-20'),
        text: 'Chapter 5',
    );

    $notification = new NewExam($exam);

    expect($notification->exam)->toBe($exam)
        ->and($notification->exam->subject)->toBe('Physics');
});

it('sends NewExam via Discord channel', function () {
    $exam = Exam::create(
        id: 1,
        subject: 'Physics',
        date: CarbonImmutable::parse('2026-03-20'),
        text: 'Chapter 5',
    );

    $notification = new NewExam($exam);

    expect($notification->via(new stdClass))->toBe([DiscordChannel::class]);
});

it('formats NewExam Discord message correctly', function () {
    $exam = Exam::create(
        id: 1,
        subject: 'Englisch',
        date: CarbonImmutable::parse('2026-04-01'),
        text: 'Grammar test',
    );

    $notification = new NewExam($exam);
    $message = $notification->toDiscord($notification);

    expect($message->body)->toContain('Englisch')
        ->and($message->body)->toContain('01.04.2026');
});

// NewHomework notification
it('creates a NewHomework notification with homework', function () {
    $homework = Homework::create(
        id: 1,
        subject: 'Biology',
        date: Carbon::parse('2026-03-10'),
        dueDate: Carbon::parse('2026-03-17'),
        text: 'Read pages 50-60',
        done: false,
    );

    $notification = new NewHomework($homework);

    expect($notification->homework)->toBe($homework)
        ->and($notification->homework->subject)->toBe('Biology');
});

it('sends NewHomework via Discord channel', function () {
    $homework = Homework::create(
        id: 1,
        subject: 'Biology',
        date: Carbon::parse('2026-03-10'),
        dueDate: Carbon::parse('2026-03-17'),
        text: 'Read pages 50-60',
        done: false,
    );

    $notification = new NewHomework($homework);

    expect($notification->via(new stdClass))->toBe([DiscordChannel::class]);
});

it('formats NewHomework Discord message correctly', function () {
    $homework = Homework::create(
        id: 1,
        subject: 'Kunst',
        date: Carbon::parse('2026-03-10'),
        dueDate: Carbon::parse('2026-03-24'),
        text: 'Zeichne ein Bild',
        done: false,
    );

    $notification = new NewHomework($homework);
    $message = $notification->toDiscord($notification);

    expect($message->body)->toContain('Kunst')
        ->and($message->body)->toContain('24.03.2026')
        ->and($message->body)->toContain('Zeichne ein Bild');
});

// NewNews notification
it('creates a NewNews notification with news', function () {
    $news = News::create(id: 1, subject: 'Alert', text: 'School closed');

    $notification = new NewNews($news);

    expect($notification->news)->toBe($news)
        ->and($notification->news->subject)->toBe('Alert');
});

it('sends NewNews via Discord channel', function () {
    $news = News::create(id: 1, subject: 'Alert', text: 'School closed');

    $notification = new NewNews($news);

    expect($notification->via(new stdClass))->toBe([DiscordChannel::class]);
});

it('formats NewNews Discord message with subject', function () {
    $news = News::create(id: 1, subject: 'Wichtig', text: 'Elternabend morgen');

    $notification = new NewNews($news);
    $message = $notification->toDiscord($notification);

    expect($message->body)->toContain('**Wichtig:**')
        ->and($message->body)->toContain('Elternabend morgen');
});

it('formats NewNews Discord message without subject', function () {
    $news = News::create(id: 1, subject: '', text: 'General announcement');

    $notification = new NewNews($news);
    $message = $notification->toDiscord($notification);

    expect($message->body)->toBe('General announcement');
});
