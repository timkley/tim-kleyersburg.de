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

it('authenticates successfully', function () {
    Http::fake([
        'jrsn.webuntis.com/*' => Http::response([
            'jsonrpc' => '2.0',
            'id' => '1',
            'result' => [
                'sessionId' => '214E1E3CBB8F28118B613ABD61D89EE9',
                'personType' => 5,
                'personId' => 9123,
                'klasseId' => 1587,
            ],
        ]),
    ]);

    $untis = app(Untis::class);

    expect($untis->sessionId)->toBe('214E1E3CBB8F28118B613ABD61D89EE9')
        ->and($untis->personType)->toBe(5)
        ->and($untis->personId)->toBe(9123)
        ->and($untis->klasseId)->toBe(1587);
});

it('encodes schoolname cookie with base64 and underscore prefix', function () {
    $school = 'jrsn';
    $expectedCookie = '_'.base64_encode($school);

    expect($expectedCookie)->toBe('_anJzbg==');
});

it('fetches news and returns correct structure', function () {
    Http::fake([
        'jrsn.webuntis.com/WebUntis/jsonrpc.do*' => Http::response([
            'jsonrpc' => '2.0',
            'id' => '1',
            'result' => [
                'sessionId' => '214E1E3CBB8F28118B613ABD61D89EE9',
                'personType' => 5,
                'personId' => 9123,
                'klasseId' => 1587,
            ],
        ]),
        'jrsn.webuntis.com/WebUntis/api/public/news/newsWidgetData*' => Http::response([
            'data' => [
                'systemMessage' => null,
                'messagesOfDay' => [
                    [
                        'id' => 12345,
                        'subject' => 'Wichtige Mitteilung',
                        'text' => '<p>Der Unterricht am Freitag entfällt wegen einer Fortbildung.</p>',
                    ],
                    [
                        'id' => 12346,
                        'subject' => 'Elternabend',
                        'text' => 'Am 20. Januar findet der Elternabend statt.',
                    ],
                ],
                'rssUrl' => 'NewsFeed.do?school=jrsn',
            ],
        ]),
    ]);

    $untis = app(Untis::class);
    $news = $untis->news();

    expect($news)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($news)->toHaveCount(2);

    $firstNews = $news->first();
    expect($firstNews)->toBeInstanceOf(News::class)
        ->and($firstNews->id)->toBe(12345)
        ->and($firstNews->subject)->toBe('Wichtige Mitteilung')
        ->and($firstNews->text)->toBe('Der Unterricht am Freitag entfällt wegen einer Fortbildung.');

    $secondNews = $news->last();
    expect($secondNews->id)->toBe(12346)
        ->and($secondNews->subject)->toBe('Elternabend');
});

it('fetches timetable and returns correct structure', function () {
    Http::fake([
        'jrsn.webuntis.com/WebUntis/jsonrpc.do*' => Http::sequence()
            ->push([
                'jsonrpc' => '2.0',
                'id' => '1',
                'result' => [
                    'sessionId' => '214E1E3CBB8F28118B613ABD61D89EE9',
                    'personType' => 5,
                    'personId' => 9123,
                    'klasseId' => 1587,
                ],
            ])
            ->push([
                'jsonrpc' => '2.0',
                'id' => '1',
                'result' => [
                    [
                        'id' => 1815868,
                        'date' => 20260108,
                        'startTime' => 835,
                        'endTime' => 920,
                        'kl' => [
                            [
                                'id' => 1587,
                                'name' => '6.4',
                                'longname' => 'Sca,Gru A109',
                            ],
                        ],
                        'su' => [
                            [
                                'id' => 35,
                                'name' => 'D',
                                'longname' => 'Deutsch',
                            ],
                        ],
                        'ro' => [
                            [
                                'id' => 210,
                                'name' => 'A109',
                                'longname' => '6.4',
                            ],
                        ],
                        'lsnumber' => 3800,
                        'activityType' => 'Unterricht',
                    ],
                    [
                        'id' => 1981111,
                        'date' => 20260108,
                        'startTime' => 745,
                        'endTime' => 830,
                        'code' => 'cancelled',
                        'kl' => [
                            [
                                'id' => 1587,
                                'name' => '6.4',
                                'longname' => 'Sca,Gru A109',
                            ],
                        ],
                        'su' => [
                            [
                                'id' => 1327,
                                'name' => 'M-Werkstatt',
                                'longname' => 'Mathe-Werkstatt',
                            ],
                        ],
                        'ro' => [
                            [
                                'id' => 210,
                                'name' => 'A109',
                                'longname' => '6.4',
                            ],
                        ],
                        'lsnumber' => 204700,
                        'substText' => 'vorgezogen auf 17.12. 2h',
                        'activityType' => 'Unterricht',
                    ],
                    [
                        'id' => 1815871,
                        'date' => 20260109,
                        'startTime' => 1125,
                        'endTime' => 1210,
                        'lstext' => 'Vertretungsstunde',
                        'kl' => [
                            [
                                'id' => 1587,
                                'name' => '6.4',
                                'longname' => 'Sca,Gru A109',
                            ],
                        ],
                        'ro' => [
                            [
                                'id' => 210,
                                'name' => 'A109',
                                'longname' => '6.4',
                            ],
                        ],
                        'lsnumber' => 3800,
                        'activityType' => 'Unterricht',
                    ],
                ],
            ]),
    ]);

    $untis = app(Untis::class);
    $startDate = CarbonImmutable::create(2026, 1, 8);
    $lessons = $untis->timetable($startDate, $startDate);

    expect($lessons)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($lessons)->toHaveCount(3);

    $firstLesson = $lessons->first();
    expect($firstLesson)->toBeInstanceOf(Lesson::class)
        ->and($firstLesson->id)->toBe(1815868)
        ->and($firstLesson->subject)->toBe('Deutsch')
        ->and($firstLesson->start)->toBeInstanceOf(Illuminate\Support\Carbon::class)
        ->and($firstLesson->start->format('Y-m-d H:i'))->toBe('2026-01-08 08:35')
        ->and($firstLesson->end->format('Y-m-d H:i'))->toBe('2026-01-08 09:20')
        ->and($firstLesson->cancelled)->toBeFalse();

    $cancelledLesson = $lessons->get(1);
    expect($cancelledLesson->subject)->toBe('Mathe-Werkstatt')
        ->and($cancelledLesson->cancelled)->toBeTrue();

    $substitutionLesson = $lessons->last();
    expect($substitutionLesson->subject)->toBe('Vertretungsstunde');
});

it('fetches homeworks and returns correct structure', function () {
    Http::fake([
        'jrsn.webuntis.com/WebUntis/jsonrpc.do*' => Http::response([
            'jsonrpc' => '2.0',
            'id' => '1',
            'result' => [
                'sessionId' => '214E1E3CBB8F28118B613ABD61D89EE9',
                'personType' => 5,
                'personId' => 9123,
                'klasseId' => 1587,
            ],
        ]),
        'jrsn.webuntis.com/WebUntis/api/homeworks/lessons*' => Http::response([
            'data' => [
                'records' => [
                    [
                        'homeworkId' => 27479,
                        'teacherId' => 374,
                        'elementIds' => [9123],
                    ],
                ],
                'homeworks' => [
                    [
                        'id' => 27479,
                        'lessonId' => 85929,
                        'date' => 20260107,
                        'dueDate' => 20260116,
                        'text' => 'vocabulary test (über S. 203 - 205 "match")',
                        'remark' => '',
                        'completed' => false,
                        'attachments' => [],
                    ],
                    [
                        'id' => 27368,
                        'lessonId' => 85504,
                        'date' => 20251215,
                        'dueDate' => 20260109,
                        'text' => 'KA Deutsch unterschrieben mitbringen',
                        'remark' => '',
                        'completed' => true,
                        'attachments' => [],
                    ],
                ],
                'teachers' => [
                    [
                        'id' => 374,
                        'name' => 'Sbg',
                    ],
                    [
                        'id' => 130,
                        'name' => 'Gru',
                    ],
                ],
                'lessons' => [
                    [
                        'id' => 85929,
                        'subject' => 'E',
                        'lessonType' => 'Unterricht',
                    ],
                    [
                        'id' => 85504,
                        'subject' => 'D',
                        'lessonType' => 'Unterricht',
                    ],
                ],
            ],
        ]),
    ]);

    $untis = app(Untis::class);
    $startDate = CarbonImmutable::create(2026, 1, 7);
    $endDate = $startDate->addWeeks(2);
    $homeworks = $untis->homeworks($startDate, $endDate);

    expect($homeworks)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($homeworks)->toHaveCount(2);

    $firstHomework = $homeworks->first();
    expect($firstHomework)->toBeInstanceOf(Homework::class)
        ->and($firstHomework->id)->toBe(27368)
        ->and($firstHomework->subject)->toBe('D')
        ->and($firstHomework->date)->toBeInstanceOf(Illuminate\Support\Carbon::class)
        ->and($firstHomework->date->format('Y-m-d'))->toBe('2025-12-15')
        ->and($firstHomework->dueDate->format('Y-m-d'))->toBe('2026-01-09')
        ->and($firstHomework->text)->toBe('KA Deutsch unterschrieben mitbringen')
        ->and($firstHomework->done)->toBeTrue();

    $secondHomework = $homeworks->last();
    expect($secondHomework->id)->toBe(27479)
        ->and($secondHomework->subject)->toBe('E')
        ->and($secondHomework->dueDate->format('Y-m-d'))->toBe('2026-01-16')
        ->and($secondHomework->done)->toBeFalse();
});

it('fetches exams and returns correct structure', function () {
    Http::fake([
        'jrsn.webuntis.com/WebUntis/jsonrpc.do*' => Http::response([
            'jsonrpc' => '2.0',
            'id' => '1',
            'result' => [
                'sessionId' => '214E1E3CBB8F28118B613ABD61D89EE9',
                'personType' => 5,
                'personId' => 9123,
                'klasseId' => 1587,
            ],
        ]),
        'jrsn.webuntis.com/WebUntis/api/exams*' => Http::response([
            'data' => [
                'exams' => [
                    [
                        'id' => 0,
                        'examType' => 'KA',
                        'name' => 'AES',
                        'studentClass' => ['6.4'],
                        'assignedStudents' => [
                            [
                                'disadvantageCompensation' => false,
                                'gradeProtection' => false,
                                'id' => 9123,
                                'displayName' => 'Herrmann Emilie',
                                'klasse' => [
                                    'id' => 0,
                                    'name' => '',
                                ],
                            ],
                        ],
                        'examDate' => 20260115,
                        'startTime' => 1125,
                        'endTime' => 1300,
                        'subject' => 'AES',
                        'teachers' => ['Wei'],
                        'rooms' => ['A109', 'A109'],
                        'text' => '1. schriftliche KA',
                        'grade' => '',
                    ],
                    [
                        'id' => 0,
                        'examType' => 'KA',
                        'name' => 'Klassenarbeit 3',
                        'studentClass' => ['6.4'],
                        'assignedStudents' => [
                            [
                                'disadvantageCompensation' => false,
                                'gradeProtection' => false,
                                'id' => 9123,
                                'displayName' => 'Herrmann Emilie',
                                'klasse' => [
                                    'id' => 0,
                                    'name' => '',
                                ],
                            ],
                        ],
                        'examDate' => 20260224,
                        'startTime' => 1125,
                        'endTime' => 1300,
                        'subject' => 'M',
                        'teachers' => ['Bru'],
                        'rooms' => ['A109'],
                        'text' => '',
                        'grade' => '',
                    ],
                ],
            ],
        ]),
    ]);

    $untis = app(Untis::class);
    $startDate = CarbonImmutable::create(2026, 1, 13);
    $endDate = $startDate->addMonths(2);
    $exams = $untis->exams($startDate, $endDate);

    expect($exams)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($exams)->toHaveCount(2);

    $firstExam = $exams->first();
    expect($firstExam)->toBeInstanceOf(Exam::class)
        ->and($firstExam->id)->toBe(0)
        ->and($firstExam->subject)->toBe('AES')
        ->and($firstExam->date)->toBeInstanceOf(CarbonImmutable::class)
        ->and($firstExam->date->format('Y-m-d'))->toBe('2026-01-15')
        ->and($firstExam->text)->toBe('1. schriftliche KA');

    $secondExam = $exams->last();
    expect($secondExam->subject)->toBe('M')
        ->and($secondExam->date->format('Y-m-d'))->toBe('2026-02-24');
});

it('caches the session to avoid repeated logins', function () {
    $requestCount = 0;

    Http::fake(function () use (&$requestCount) {
        $requestCount++;

        return Http::response([
            'jsonrpc' => '2.0',
            'id' => '1',
            'result' => [
                'sessionId' => 'CACHED_SESSION_ID_123',
                'personType' => 5,
                'personId' => 9123,
                'klasseId' => 1587,
            ],
        ]);
    });

    $untis1 = app(Untis::class);
    $sessionId1 = $untis1->sessionId;

    $untis2 = app(Untis::class);
    $sessionId2 = $untis2->sessionId;

    expect($sessionId1)->toBe($sessionId2)
        ->and($sessionId1)->toBe('CACHED_SESSION_ID_123')
        ->and($requestCount)->toBe(1);
});

it('re-authenticates when session expires', function () {
    $callCount = 0;

    Http::fake(function ($request) use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response([
                'jsonrpc' => '2.0',
                'id' => '1',
                'result' => [
                    'sessionId' => 'INITIAL_SESSION',
                    'personType' => 5,
                    'personId' => 9123,
                    'klasseId' => 1587,
                ],
            ]);
        }

        if ($callCount === 2) {
            return Http::response([
                'jsonrpc' => '2.0',
                'id' => 'error',
                'error' => [
                    'message' => 'not authenticated',
                    'code' => -8520,
                ],
            ]);
        }

        if ($callCount === 3) {
            return Http::response([
                'jsonrpc' => '2.0',
                'id' => '1',
                'result' => [
                    'sessionId' => 'NEW_SESSION_AFTER_REAUTH',
                    'personType' => 5,
                    'personId' => 9123,
                    'klasseId' => 1587,
                ],
            ]);
        }

        return Http::response([
            'data' => [
                'messagesOfDay' => [],
            ],
        ]);
    });

    $untis = app(Untis::class);
    expect($untis->sessionId)->toBe('INITIAL_SESSION');

    cache()->forget('untis.sessionid');

    $untis->news();

    expect($untis->sessionId)->toBe('NEW_SESSION_AFTER_REAUTH');
});

it('handles empty responses gracefully', function () {
    Http::fake([
        'jrsn.webuntis.com/WebUntis/jsonrpc.do*' => Http::response([
            'jsonrpc' => '2.0',
            'id' => '1',
            'result' => [
                'sessionId' => '214E1E3CBB8F28118B613ABD61D89EE9',
                'personType' => 5,
                'personId' => 9123,
                'klasseId' => 1587,
            ],
        ]),
        'jrsn.webuntis.com/WebUntis/api/public/news/newsWidgetData*' => Http::response([
            'data' => [
                'messagesOfDay' => [],
            ],
        ]),
        'jrsn.webuntis.com/WebUntis/api/homeworks/lessons*' => Http::response([
            'data' => [
                'homeworks' => [],
                'lessons' => [],
            ],
        ]),
        'jrsn.webuntis.com/WebUntis/api/exams*' => Http::response([
            'data' => [
                'exams' => [],
            ],
        ]),
    ]);

    $untis = app(Untis::class);
    $startDate = CarbonImmutable::now()->startOfWeek();

    expect($untis->news())->toBeEmpty()
        ->and($untis->homeworks($startDate, $startDate->addWeeks(2)))->toBeEmpty()
        ->and($untis->exams($startDate, $startDate->addMonths(2)))->toBeEmpty();
});
