<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Untis\Exam;
use App\Data\Untis\Homework;
use App\Data\Untis\Lesson;
use App\Data\Untis\News;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Untis
{
    public string $sessionId;

    public int $personType;

    public int $personId;

    public int $klasseId;

    public function __construct(public string $server, public string $school, public string $username, public string $password)
    {
        $this->login();
    }

    public function news(): Collection
    {
        $response = $this->request(
            url: 'api/public/news/newsWidgetData',
            parameters: [
                'date' => today()->format('Ymd'),
            ]
        );

        return collect(data_get($response, 'data.messagesOfDay'))
            ->map(fn ($news) => News::createFromApi($news));
    }

    public function timetable(CarbonImmutable $startDate, CarbonImmutable $endDate): Collection
    {
        $response = $this->request(
            method: 'post',
            data: [
                'id' => '1',
                'method' => 'getTimetable',
                'params' => [
                    'options' => [
                        'id' => '1',
                        'element' => [
                            'id' => $this->personId,
                            'type' => $this->personType,
                        ],
                        'startDate' => $startDate->format('Ymd'),
                        'endDate' => $endDate->addDays(6)->format('Ymd'),
                        'showLsText' => true,
                        'showStudentgroup' => true,
                        'showLsNumber' => true,
                        'showSubstText' => true,
                        'showInfo' => true,
                        'showBooking' => true,
                        'klasseFields' => ['id', 'name', 'longname', 'externalkey'],
                        'roomFields' => ['id', 'name', 'longname', 'externalkey'],
                        'subjectFields' => ['id', 'name', 'longname', 'externalkey'],
                        'teacherFields' => ['id', 'name', 'longname', 'externalkey'],
                    ],
                ],
            ]
        );

        return collect(data_get($response, 'result'))
            ->map(fn ($lesson) => Lesson::createFromApi($lesson));
    }

    public function homeworks(CarbonImmutable $startDate, CarbonImmutable $endDate): Collection
    {
        $response = $this->request(
            url: 'api/homeworks/lessons',
            parameters: [
                'startDate' => $startDate->format('Ymd'),
                'endDate' => $endDate->format('Ymd'),
            ]
        );

        try {
            $homeworks = collect($response['data']['homeworks'])->sortBy('dueDate');
            $lessons = collect($response['data']['lessons']);
        } catch (Exception $e) {
            return collect();
        }

        $lessonsLookup = $lessons->keyBy('id');

        $mergedHomeworks = $homeworks->map(function ($homework) use ($lessonsLookup) {
            $lesson = $lessonsLookup->get($homework['lessonId']);

            if ($lesson) {
                $homework['lesson'] = $lesson;
            }

            return $homework;
        });
        $response['data']['homeworks'] = $mergedHomeworks->toArray();

        return collect(data_get($response, 'data.homeworks'))
            ->map(fn ($homework) => Homework::createFromApi($homework));
    }

    public function exams(CarbonImmutable $startDate, CarbonImmutable $endDate): Collection
    {
        $response = $this->request(
            url: 'api/exams',
            parameters: [
                'startDate' => $startDate->format('Ymd'),
                'endDate' => $endDate->format('Ymd'),
                'klasseId' => $this->klasseId,
            ]
        );

        return collect(data_get($response, 'data.exams'))
            ->map(fn ($exam) => Exam::createFromApi($exam));
    }

    public function request(string $method = 'get', string $url = 'jsonrpc.do', array $data = [], array $parameters = [])
    {
        $response = Http::beforeSending(fn (Request $request) => logger()->channel('untis')->debug('Request', ['url' => $request->url(), 'data' => $request->data(), 'method' => $request->method()]))
            ->withoutRedirecting()
            ->asJson()
            ->baseUrl("https://$this->server.webuntis.com/WebUntis")
            ->withQueryParameters(array_merge($parameters, ['school' => $this->school]))
            ->withCookies([
                'schoolname' => $this->school,
                'JSESSIONID' => $this->sessionId ?? '',
            ], "$this->server.webuntis.com")
            ->{$method}(
                $url,
                array_merge($data, ['jsonrpc' => '2.0'])
            );

        logger()->channel('untis')->debug('Response', ['status' => $response->status(), 'body' => $response->json()]);

        $unauthenticated = data_get($response, 'error.code') === -8520;

        if ($unauthenticated) {
            $this->login();

            return $this->request(...);
        }

        return $response->json();
    }

    private function login(): void
    {
        $loginData = cache()->remember('untis.sessionid', now()->addMinutes(10), function () {
            $response = retry(3, function () {
                $response = $this->request(method: 'post', data: [
                    'id' => '1',
                    'method' => 'authenticate',
                    'params' => [
                        'user' => $this->username,
                        'password' => $this->password,
                        'client' => 'WebUntis Test',
                    ],
                ]);

                if (data_get($response, 'error.code') === -8520 || ! data_get($response, 'result.sessionId')) {
                    throw new Exception('Login failed');
                }

                return $response;
            }, function (int $attempt, Exception $exception) {
                return $attempt * 10000;
            });

            return [
                data_get($response, 'result.sessionId'),
                data_get($response, 'result.personType'),
                data_get($response, 'result.personId'),
                data_get($response, 'result.klasseId'),
            ];
        });

        [$this->sessionId, $this->personType, $this->personId, $this->klasseId] = $loginData;
    }
}
