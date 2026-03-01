<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Modules\Holocron\School\Data\Exam;
use Modules\Holocron\School\Data\Homework;
use Modules\Holocron\School\Data\Lesson;
use Modules\Holocron\School\Data\News;

// Exam DTO
it('creates an Exam via constructor', function () {
    $date = CarbonImmutable::parse('2026-03-15');
    $exam = new Exam(id: 1, subject: 'Math', date: $date, text: 'Chapter 5 test');

    expect($exam->id)->toBe(1)
        ->and($exam->subject)->toBe('Math')
        ->and($exam->date)->toBe($date)
        ->and($exam->text)->toBe('Chapter 5 test');
});

it('creates an Exam via static create method', function () {
    $date = CarbonImmutable::parse('2026-03-15');
    $exam = Exam::create(id: 2, subject: 'English', date: $date, text: 'Essay');

    expect($exam)->toBeInstanceOf(Exam::class)
        ->and($exam->id)->toBe(2)
        ->and($exam->subject)->toBe('English');
});

it('creates an Exam from API data', function () {
    $exam = Exam::createFromApi([
        'id' => 42,
        'subject' => 'Physics',
        'examDate' => '20260315',
        'text' => 'Optics unit test',
    ]);

    expect($exam->id)->toBe(42)
        ->and($exam->subject)->toBe('Physics')
        ->and($exam->date->format('Y-m-d'))->toBe('2026-03-15')
        ->and($exam->text)->toBe('Optics unit test');
});

// Homework DTO
it('creates a Homework via constructor', function () {
    $date = Carbon::parse('2026-03-10');
    $dueDate = Carbon::parse('2026-03-17');
    $homework = new Homework(id: 1, subject: 'German', date: $date, dueDate: $dueDate, text: 'Page 42', done: false);

    expect($homework->id)->toBe(1)
        ->and($homework->subject)->toBe('German')
        ->and($homework->date)->toBe($date)
        ->and($homework->dueDate)->toBe($dueDate)
        ->and($homework->text)->toBe('Page 42')
        ->and($homework->done)->toBeFalse();
});

it('creates a Homework via static create method', function () {
    $date = Carbon::parse('2026-03-10');
    $dueDate = Carbon::parse('2026-03-17');
    $homework = Homework::create(id: 5, subject: 'Math', date: $date, dueDate: $dueDate, text: 'Exercises 1-10', done: true);

    expect($homework)->toBeInstanceOf(Homework::class)
        ->and($homework->done)->toBeTrue();
});

it('creates a Homework from API data', function () {
    $homework = Homework::createFromApi([
        'id' => 99,
        'lesson' => ['subject' => 'Biology'],
        'date' => '20260310',
        'dueDate' => '20260317',
        'text' => 'Read chapter 3',
        'completed' => 0,
    ]);

    expect($homework->id)->toBe(99)
        ->and($homework->subject)->toBe('Biology')
        ->and($homework->date->format('Y-m-d'))->toBe('2026-03-10')
        ->and($homework->dueDate->format('Y-m-d'))->toBe('2026-03-17')
        ->and($homework->text)->toBe('Read chapter 3')
        ->and($homework->done)->toBeFalse();
});

it('creates a completed Homework from API data', function () {
    $homework = Homework::createFromApi([
        'id' => 100,
        'lesson' => ['subject' => 'Art'],
        'date' => '20260310',
        'dueDate' => '20260317',
        'text' => 'Draw a landscape',
        'completed' => 1,
    ]);

    expect($homework->done)->toBeTrue();
});

// Lesson DTO
it('creates a Lesson via constructor', function () {
    $start = Carbon::parse('2026-03-10 08:00');
    $end = Carbon::parse('2026-03-10 08:45');
    $lesson = new Lesson(id: 1, subject: 'Math', start: $start, end: $end, cancelled: false);

    expect($lesson->id)->toBe(1)
        ->and($lesson->subject)->toBe('Math')
        ->and($lesson->start)->toBe($start)
        ->and($lesson->end)->toBe($end)
        ->and($lesson->cancelled)->toBeFalse();
});

it('creates a Lesson via static create method', function () {
    $start = Carbon::parse('2026-03-10 08:00');
    $end = Carbon::parse('2026-03-10 08:45');
    $lesson = Lesson::create(id: 10, subject: 'English', start: $start, end: $end, cancelled: true);

    expect($lesson)->toBeInstanceOf(Lesson::class)
        ->and($lesson->cancelled)->toBeTrue();
});

it('creates a Lesson from API data with longname subject', function () {
    $lesson = Lesson::createFromApi([
        'id' => 55,
        'su' => [['longname' => 'Mathematics']],
        'date' => '20260310',
        'startTime' => 800,
        'endTime' => 845,
    ]);

    expect($lesson->id)->toBe(55)
        ->and($lesson->subject)->toBe('Mathematics')
        ->and($lesson->start->format('H:i'))->toBe('08:00')
        ->and($lesson->end->format('H:i'))->toBe('08:45')
        ->and($lesson->cancelled)->toBeFalse();
});

it('creates a Lesson from API data with lstext fallback', function () {
    $lesson = Lesson::createFromApi([
        'id' => 56,
        'su' => [],
        'lstext' => 'Sport',
        'date' => '20260310',
        'startTime' => 945,
        'endTime' => 1030,
    ]);

    expect($lesson->subject)->toBe('Sport');
});

it('creates a cancelled Lesson from API data', function () {
    $lesson = Lesson::createFromApi([
        'id' => 57,
        'su' => [['longname' => 'German']],
        'date' => '20260310',
        'startTime' => 1100,
        'endTime' => 1145,
        'code' => 'cancelled',
    ]);

    expect($lesson->cancelled)->toBeTrue();
});

it('pads short start and end times in Lesson', function () {
    $lesson = Lesson::createFromApi([
        'id' => 58,
        'su' => [['longname' => 'History']],
        'date' => '20260310',
        'startTime' => 800,
        'endTime' => 845,
    ]);

    expect($lesson->start->format('H:i'))->toBe('08:00')
        ->and($lesson->end->format('H:i'))->toBe('08:45');
});

// News DTO
it('creates a News via constructor', function () {
    $news = new News(id: 1, subject: 'Announcement', text: 'School closed tomorrow');

    expect($news->id)->toBe(1)
        ->and($news->subject)->toBe('Announcement')
        ->and($news->text)->toBe('School closed tomorrow');
});

it('creates a News via static create method', function () {
    $news = News::create(id: 3, subject: 'Event', text: 'Sports day Friday');

    expect($news)->toBeInstanceOf(News::class)
        ->and($news->id)->toBe(3);
});

it('creates a News from API data', function () {
    $news = News::createFromApi([
        'id' => 77,
        'subject' => 'Info',
        'text' => '<b>Important:</b> Parent meeting',
    ]);

    expect($news->id)->toBe(77)
        ->and($news->subject)->toBe('Info')
        ->and($news->text)->toBe('Important: Parent meeting');
});

it('strips HTML tags from News text', function () {
    $news = News::createFromApi([
        'id' => 78,
        'subject' => 'Alert',
        'text' => '<p>Fire <strong>drill</strong> at 10am</p>',
    ]);

    expect($news->text)->toBe('Fire drill at 10am');
});
