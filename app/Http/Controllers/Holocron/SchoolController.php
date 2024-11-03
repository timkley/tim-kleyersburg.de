<?php

namespace App\Http\Controllers\Holocron;

use App\Http\Controllers\Controller;
use App\Services\Untis;
use Illuminate\Support\Carbon;

class SchoolController extends Controller
{
    private Untis $untis;

    public function __construct()
    {
        $this->untis = resolve(Untis::class);
    }

    public function __invoke()
    {
        return view('holocron.school.index', [
            'news' => $this->news(),
            'homeworks' => $this->homeworks(),
            'exams' => $this->exams(),
            'timetable' => $this->timetable(),
        ]);
    }

    private function news()
    {
        return cache()->remember('holocron.school.news', now()->addMinutes(15), fn () => $this->untis->news());
    }

    private function timetable()
    {
        $timetable = cache()->flexible('holocron.school.timetable', [now()->addMinutes(15), now()->addYear()], fn () => $this->untis->timetable(now(), now()->addDays(6)));

        return collect($timetable['result'])->map(function ($lesson) {
            $start = Carbon::createFromFormat('Ymd Hi', $lesson['date'].' '.str_pad($lesson['startTime'], 4, '0', STR_PAD_LEFT));
            $end = Carbon::createFromFormat('Ymd Hi', $lesson['date'].' '.str_pad($lesson['endTime'], 4, '0', STR_PAD_LEFT));

            return [
                'id' => $lesson['id'],
                'subject' => data_get($lesson, 'su.0.longname'),
                'start' => $start,
                'end' => $end,
                'cancelled' => data_get($lesson, 'code') === 'cancelled',
            ];
        });
    }

    private function homeworks()
    {
        return cache()->flexible('holocron.school.homeworks', [now()->addMinutes(15), now()->addYear()], fn () => $this->untis->homeworks(today()->subDays(21), today()->addDays(21)));
    }

    private function exams()
    {
        return cache()->flexible('holocron.school.exams', [now()->addMinutes(15), now()->addYear()], fn () => $this->untis->exams(today(), today()->addDays(21)));
    }
}
