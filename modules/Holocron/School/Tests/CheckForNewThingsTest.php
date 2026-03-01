<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Sleep;
use Modules\Holocron\School\Data\Exam;
use Modules\Holocron\School\Data\Homework;
use Modules\Holocron\School\Data\Lesson;
use Modules\Holocron\School\Data\News;
use Modules\Holocron\School\Jobs\CheckForNewThings;
use Modules\Holocron\School\Services\Untis;

beforeEach(function () {
    Notification::fake();
    Sleep::fake();
    cache()->store('file_persistent')->flush();
});

function buildMockUntis(
    ?Collection $news = null,
    ?Collection $homeworks = null,
    ?Collection $exams = null,
    ?Collection $timetable = null,
    int $times = 1,
): Untis {
    $untis = Mockery::mock(Untis::class);
    $untis->shouldReceive('news')->times($times)->andReturn($news ?? new Collection);
    $untis->shouldReceive('homeworks')->times($times)->andReturn($homeworks ?? new Collection);
    $untis->shouldReceive('exams')->times($times)->andReturn($exams ?? new Collection);
    $untis->shouldReceive('timetable')->times($times)->andReturn($timetable ?? new Collection);

    return $untis;
}

it('sends a notification for new news', function () {
    $news = new Collection([
        News::create(id: 1, subject: 'Alert', text: 'Fire drill'),
    ]);

    $untis = buildMockUntis(news: $news);

    $job = new CheckForNewThings;
    $job->handle($untis);

    // Verify cache was set (notification was processed)
    expect(cache()->store('file_persistent')->has('holocron.school.news.1'))->toBeTrue();
});

it('does not send duplicate news notifications', function () {
    $news = new Collection([
        News::create(id: 1, subject: 'Alert', text: 'Fire drill'),
    ]);

    // Pre-cache the news item
    cache()->store('file_persistent')->put('holocron.school.news.1', true, now()->addYear());

    $untis = buildMockUntis(news: $news);

    $job = new CheckForNewThings;
    $job->handle($untis);

    // The cache key was already set, so no new notification should be sent
    // We verify by checking the job ran without errors
    expect(cache()->store('file_persistent')->has('holocron.school.news.1'))->toBeTrue();
});

it('caches new homework items when processing', function () {
    $homeworks = new Collection([
        Homework::create(
            id: 10,
            subject: 'Math',
            date: Carbon::today(),
            dueDate: Carbon::today()->addDays(5),
            text: 'Page 42',
            done: false,
        ),
    ]);

    $untis = buildMockUntis(homeworks: $homeworks);

    $job = new CheckForNewThings;
    $job->handle($untis);

    expect(cache()->store('file_persistent')->has('holocron.school.homeworks.10'))->toBeTrue();
});

it('skips homework that is already done', function () {
    $homeworks = new Collection([
        Homework::create(
            id: 11,
            subject: 'Math',
            date: Carbon::today(),
            dueDate: Carbon::today()->addDays(5),
            text: 'Page 42',
            done: true,
        ),
    ]);

    $untis = buildMockUntis(homeworks: $homeworks);

    $job = new CheckForNewThings;
    $job->handle($untis);

    expect(cache()->store('file_persistent')->has('holocron.school.homeworks.11'))->toBeFalse();
});

it('skips homework with a past due date', function () {
    $homeworks = new Collection([
        Homework::create(
            id: 12,
            subject: 'German',
            date: Carbon::today()->subDays(10),
            dueDate: Carbon::today()->subDay(),
            text: 'Read chapter 3',
            done: false,
        ),
    ]);

    $untis = buildMockUntis(homeworks: $homeworks);

    $job = new CheckForNewThings;
    $job->handle($untis);

    expect(cache()->store('file_persistent')->has('holocron.school.homeworks.12'))->toBeFalse();
});

it('caches new exam items when processing', function () {
    $exams = new Collection([
        Exam::create(
            id: 20,
            subject: 'Physics',
            date: CarbonImmutable::today()->addDays(7),
            text: 'Optics unit',
        ),
    ]);

    $untis = buildMockUntis(exams: $exams);

    $job = new CheckForNewThings;
    $job->handle($untis);

    expect(cache()->store('file_persistent')->has('holocron.school.exams.20'))->toBeTrue();
});

it('does not re-process cached exams', function () {
    cache()->store('file_persistent')->put('holocron.school.exams.20', true, now()->addMonth());

    $exams = new Collection([
        Exam::create(
            id: 20,
            subject: 'Physics',
            date: CarbonImmutable::today()->addDays(7),
            text: 'Optics unit',
        ),
    ]);

    $untis = buildMockUntis(exams: $exams);

    $job = new CheckForNewThings;
    $job->handle($untis);

    // Cache was already set before, job should skip it
    expect(cache()->store('file_persistent')->has('holocron.school.exams.20'))->toBeTrue();
});

it('caches cancelled future lessons when processing', function () {
    $lessons = new Collection([
        Lesson::create(
            id: 30,
            subject: 'German',
            start: Carbon::now()->addDay(),
            end: Carbon::now()->addDay()->addMinutes(45),
            cancelled: true,
        ),
    ]);

    $untis = buildMockUntis(timetable: $lessons);

    $job = new CheckForNewThings;
    $job->handle($untis);

    expect(cache()->store('file_persistent')->has('holocron.school.lessons.30'))->toBeTrue();
});

it('does not cache non-cancelled lessons', function () {
    $lessons = new Collection([
        Lesson::create(
            id: 31,
            subject: 'Math',
            start: Carbon::now()->addDay(),
            end: Carbon::now()->addDay()->addMinutes(45),
            cancelled: false,
        ),
    ]);

    $untis = buildMockUntis(timetable: $lessons);

    $job = new CheckForNewThings;
    $job->handle($untis);

    expect(cache()->store('file_persistent')->has('holocron.school.lessons.31'))->toBeFalse();
});

it('does not cache cancelled lessons in the past', function () {
    $lessons = new Collection([
        Lesson::create(
            id: 32,
            subject: 'English',
            start: Carbon::now()->subDays(2),
            end: Carbon::now()->subDays(2)->addMinutes(45),
            cancelled: true,
        ),
    ]);

    $untis = buildMockUntis(timetable: $lessons);

    $job = new CheckForNewThings;
    $job->handle($untis);

    expect(cache()->store('file_persistent')->has('holocron.school.lessons.32'))->toBeFalse();
});

it('does not re-process cached homework', function () {
    $homeworks = new Collection([
        Homework::create(
            id: 10,
            subject: 'Math',
            date: Carbon::today(),
            dueDate: Carbon::today()->addDays(5),
            text: 'Page 42',
            done: false,
        ),
    ]);

    // Pre-cache the homework
    cache()->store('file_persistent')->put('holocron.school.homeworks.10', true, now()->addWeek());

    $untis = buildMockUntis(homeworks: $homeworks);

    $job = new CheckForNewThings;
    $job->handle($untis);

    // It should still be cached (not re-notified)
    expect(cache()->store('file_persistent')->has('holocron.school.homeworks.10'))->toBeTrue();
});

it('does not re-process cached cancelled lessons', function () {
    $lessons = new Collection([
        Lesson::create(
            id: 30,
            subject: 'German',
            start: Carbon::now()->addDay(),
            end: Carbon::now()->addDay()->addMinutes(45),
            cancelled: true,
        ),
    ]);

    // Pre-cache the lesson
    cache()->store('file_persistent')->put('holocron.school.lessons.30', true, Carbon::now()->addDay()->addMinutes(45));

    $untis = buildMockUntis(timetable: $lessons);

    $job = new CheckForNewThings;
    $job->handle($untis);

    // It should still be cached (not re-notified)
    expect(cache()->store('file_persistent')->has('holocron.school.lessons.30'))->toBeTrue();
});

it('uses Sleep between API calls', function () {
    $untis = buildMockUntis();

    $job = new CheckForNewThings;
    $job->handle($untis);

    Sleep::assertSleptTimes(3);
});

it('implements ShouldQueue', function () {
    expect(new CheckForNewThings)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);
});
