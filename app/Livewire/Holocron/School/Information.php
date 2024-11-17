<?php

namespace App\Livewire\Holocron\School;

use App\Services\Untis;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.holocron')]
class Information extends Component
{
    private Untis $untis;

    public function __construct()
    {
        $this->untis = resolve(Untis::class);
    }

    public function render()
    {
        return view('holocron.school.information', [
            'news' => $this->news(),
            'homeworks' => $this->homeworks(),
            'exams' => $this->exams(),
            'timetable' => $this->timetable(),
        ]);
    }

    private function news()
    {
        return cache()->flexible('holocron.school.news', [now()->addMinutes(15), now()->addYear()], fn () => $this->untis->news());
    }

    private function timetable()
    {
        return cache()->flexible('holocron.school.timetable', [now()->addMinutes(15), now()->addYear()], fn () => $this->untis->timetable(today(), today()->addDays(14)));
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
