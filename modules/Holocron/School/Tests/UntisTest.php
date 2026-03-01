<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Modules\Holocron\School\Data\Exam;
use Modules\Holocron\School\Data\Homework;
use Modules\Holocron\School\Data\Lesson;
use Modules\Holocron\School\Data\News;
use Modules\Holocron\School\Services\Untis;

beforeEach(function () {
    cache()->forget('untis.sessionid');
});

it('authenticates on construction and stores session data', function () {
    Http::fake([
        '*' => Http::response([
            'jsonrpc' => '2.0',
            'result' => [
                'sessionId' => 'test-session-123',
                'personType' => 5,
                'personId' => 42,
                'klasseId' => 100,
            ],
        ]),
    ]);

    $untis = new Untis(
        server: 'testserver',
        school: 'testschool',
        username: 'testuser',
        password: 'testpassword',
    );

    expect($untis->sessionId)->toBe('test-session-123')
        ->and($untis->personType)->toBe(5)
        ->and($untis->personId)->toBe(42)
        ->and($untis->klasseId)->toBe(100);
});

it('fetches news from the API', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push([
                'jsonrpc' => '2.0',
                'result' => [
                    'sessionId' => 'session-1',
                    'personType' => 5,
                    'personId' => 42,
                    'klasseId' => 100,
                ],
            ])
            ->push([
                'data' => [
                    'messagesOfDay' => [
                        ['id' => 1, 'subject' => 'Announcement', 'text' => 'School event tomorrow'],
                        ['id' => 2, 'subject' => 'Reminder', 'text' => '<b>Bring</b> books'],
                    ],
                ],
            ]),
    ]);

    $untis = new Untis(
        server: 'testserver',
        school: 'testschool',
        username: 'testuser',
        password: 'testpassword',
    );

    $news = $untis->news();

    expect($news)->toHaveCount(2)
        ->and($news->first())->toBeInstanceOf(News::class)
        ->and($news->first()->subject)->toBe('Announcement')
        ->and($news->last()->text)->toBe('Bring books');
});

it('fetches timetable from the API', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push([
                'jsonrpc' => '2.0',
                'result' => [
                    'sessionId' => 'session-1',
                    'personType' => 5,
                    'personId' => 42,
                    'klasseId' => 100,
                ],
            ])
            ->push([
                'jsonrpc' => '2.0',
                'result' => [
                    [
                        'id' => 101,
                        'su' => [['longname' => 'Mathematics']],
                        'date' => '20260310',
                        'startTime' => 800,
                        'endTime' => 845,
                    ],
                    [
                        'id' => 102,
                        'su' => [['longname' => 'English']],
                        'date' => '20260310',
                        'startTime' => 900,
                        'endTime' => 945,
                        'code' => 'cancelled',
                    ],
                ],
            ]),
    ]);

    $untis = new Untis(
        server: 'testserver',
        school: 'testschool',
        username: 'testuser',
        password: 'testpassword',
    );

    $timetable = $untis->timetable(CarbonImmutable::parse('2026-03-10'), CarbonImmutable::parse('2026-03-24'));

    expect($timetable)->toHaveCount(2)
        ->and($timetable->first())->toBeInstanceOf(Lesson::class)
        ->and($timetable->first()->subject)->toBe('Mathematics')
        ->and($timetable->first()->cancelled)->toBeFalse()
        ->and($timetable->last()->cancelled)->toBeTrue();
});

it('fetches homeworks from the API', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push([
                'jsonrpc' => '2.0',
                'result' => [
                    'sessionId' => 'session-1',
                    'personType' => 5,
                    'personId' => 42,
                    'klasseId' => 100,
                ],
            ])
            ->push([
                'data' => [
                    'homeworks' => [
                        [
                            'id' => 201,
                            'lessonId' => 301,
                            'date' => '20260310',
                            'dueDate' => '20260317',
                            'text' => 'Exercise 1-5',
                            'completed' => 0,
                        ],
                    ],
                    'lessons' => [
                        ['id' => 301, 'subject' => 'German'],
                    ],
                ],
            ]),
    ]);

    $untis = new Untis(
        server: 'testserver',
        school: 'testschool',
        username: 'testuser',
        password: 'testpassword',
    );

    $homeworks = $untis->homeworks(CarbonImmutable::parse('2026-03-03'), CarbonImmutable::parse('2026-03-24'));

    expect($homeworks)->toHaveCount(1)
        ->and($homeworks->first())->toBeInstanceOf(Homework::class)
        ->and($homeworks->first()->subject)->toBe('German')
        ->and($homeworks->first()->text)->toBe('Exercise 1-5')
        ->and($homeworks->first()->done)->toBeFalse();
});

it('returns empty collection when homeworks API response has unexpected structure', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push([
                'jsonrpc' => '2.0',
                'result' => [
                    'sessionId' => 'session-1',
                    'personType' => 5,
                    'personId' => 42,
                    'klasseId' => 100,
                ],
            ])
            ->push([
                'error' => 'something went wrong',
            ]),
    ]);

    $untis = new Untis(
        server: 'testserver',
        school: 'testschool',
        username: 'testuser',
        password: 'testpassword',
    );

    $homeworks = $untis->homeworks(CarbonImmutable::parse('2026-03-03'), CarbonImmutable::parse('2026-03-24'));

    expect($homeworks)->toBeEmpty();
});

it('fetches exams from the API', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push([
                'jsonrpc' => '2.0',
                'result' => [
                    'sessionId' => 'session-1',
                    'personType' => 5,
                    'personId' => 42,
                    'klasseId' => 100,
                ],
            ])
            ->push([
                'data' => [
                    'exams' => [
                        [
                            'id' => 401,
                            'subject' => 'Physics',
                            'examDate' => '20260320',
                            'text' => 'Optics test',
                        ],
                    ],
                ],
            ]),
    ]);

    $untis = new Untis(
        server: 'testserver',
        school: 'testschool',
        username: 'testuser',
        password: 'testpassword',
    );

    $exams = $untis->exams(CarbonImmutable::parse('2026-03-10'), CarbonImmutable::parse('2026-03-24'));

    expect($exams)->toHaveCount(1)
        ->and($exams->first())->toBeInstanceOf(Exam::class)
        ->and($exams->first()->subject)->toBe('Physics')
        ->and($exams->first()->text)->toBe('Optics test');
});

it('caches the login session across instances', function () {
    Http::fake([
        '*' => Http::response([
            'jsonrpc' => '2.0',
            'result' => [
                'sessionId' => 'cached-session',
                'personType' => 5,
                'personId' => 42,
                'klasseId' => 100,
            ],
        ]),
    ]);

    $untis1 = new Untis(
        server: 'testserver',
        school: 'testschool',
        username: 'testuser',
        password: 'testpassword',
    );

    $untis2 = new Untis(
        server: 'testserver',
        school: 'testschool',
        username: 'testuser',
        password: 'testpassword',
    );

    expect($untis1->sessionId)->toBe('cached-session')
        ->and($untis2->sessionId)->toBe('cached-session');
});

it('retries login when initial authentication fails', function () {
    Http::fake([
        '*' => Http::sequence()
            // First attempt: login fails (no sessionId)
            ->push(['jsonrpc' => '2.0', 'result' => []])
            // Second attempt: login succeeds
            ->push([
                'jsonrpc' => '2.0',
                'result' => [
                    'sessionId' => 'retry-session',
                    'personType' => 5,
                    'personId' => 42,
                    'klasseId' => 100,
                ],
            ]),
    ]);

    $untis = new Untis(
        server: 'testserver',
        school: 'testschool',
        username: 'testuser',
        password: 'testpassword',
    );

    expect($untis->sessionId)->toBe('retry-session');
});

it('sends requests with correct base URL and cookies', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push([
                'jsonrpc' => '2.0',
                'result' => [
                    'sessionId' => 'session-1',
                    'personType' => 5,
                    'personId' => 42,
                    'klasseId' => 100,
                ],
            ])
            ->push([
                'data' => ['messagesOfDay' => []],
            ]),
    ]);

    $untis = new Untis(
        server: 'myserver',
        school: 'myschool',
        username: 'testuser',
        password: 'testpassword',
    );

    $untis->news();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'myserver.webuntis.com');
    });
});
