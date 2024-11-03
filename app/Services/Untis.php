<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class Untis
{
    public string $sessionId;

    public string $personType;

    public string $personId;

    public string $klasseId;

    public function __construct(public string $server, public string $school, public string $username, public string $password)
    {
        $this->login();
    }

    public function news()
    {
        return $this->request(
            url: 'api/public/news/newsWidgetData',
            parameters: [
                'date' => today()->format('Ymd'),
            ]
        );
    }

    public function timetable(Carbon $startDate, Carbon $endDate)
    {
        return $this->request(
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
    }

    public function homeworks(Carbon $startDate, Carbon $endDate)
    {
        $response = $this->request(
            url: 'api/homeworks/lessons',
            parameters: [
                'startDate' => $startDate->format('Ymd'),
                'endDate' => $endDate->format('Ymd'),
            ]
        );

        $homeworks = collect($response['data']['homeworks'])->sortBy('dueDate');
        $lessons = collect($response['data']['lessons']);

        $lessonsLookup = $lessons->keyBy('id');

        $mergedHomeworks = $homeworks->map(function ($homework) use ($lessonsLookup) {
            $lesson = $lessonsLookup->get($homework['lessonId']);

            if ($lesson) {
                $homework['lesson'] = $lesson;
            }

            return $homework;
        });

        $response['data']['homeworks'] = $mergedHomeworks->toArray();

        return data_get($response, 'data.homeworks');
    }

    public function exams(Carbon $startDate, Carbon $endDate)
    {
        $response = $this->request(
            url: 'api/exams',
            parameters: [
                'startDate' => $startDate->format('Ymd'),
                'endDate' => $endDate->format('Ymd'),
                'klasseId' => $this->klasseId,
            ]
        );

        return data_get($response, 'data.exams');
    }

    public function request(string $method = 'get', string $url = 'jsonrpc.do', array $data = [], array $parameters = [])
    {
        $response = Http::beforeSending(fn ($request) => ray('request', $request)->color('blue'))
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

        ray('response', $response)->color('green');
        ray('response json', $response->json())->color('green');

        $unauthenticated = data_get($response, 'error.code') === -8520;

        if ($unauthenticated) {
            ray('relogin');
            $this->login();

            return $this->request(...);
        }

        return $response->json();
    }

    private function login(): void
    {
        $loginData = cache()->remember('untis.sessionid', now()->addMinutes(10), function () {
            $response = $this->request(method: 'post', data: [
                'id' => '1',
                'method' => 'authenticate',
                'params' => [
                    'user' => $this->username,
                    'password' => $this->password,
                    'client' => 'WebUntis Test',
                ],
            ]);

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
