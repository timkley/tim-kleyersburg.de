<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\School;

use App\Livewire\Holocron\HolocronComponent;
use App\Services\Untis;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Information extends HolocronComponent
{
    private Untis $untis;

    public function __construct()
    {
        $this->untis = resolve(Untis::class);
    }

    public function render(): View
    {
        return view('holocron.school.information', [
            'news' => $this->news(),
            'homeworks' => $this->homeworks(),
            'exams' => $this->exams(),
            'timetable' => $this->timetable(),
        ]);
    }

    private function news(): Collection
    {
        return cache()->flexible('holocron.school.news', [now()->addMinutes(15), now()->addYear()], fn () => $this->untis->news());
    }

    private function timetable(): Collection
    {
        return cache()->flexible('holocron.school.timetable', [now()->addMinutes(15), now()->addYear()], fn () => $this->untis->timetable(CarbonImmutable::today(), CarbonImmutable::today()->addDays(14)));
    }

    private function homeworks(): Collection
    {
        return cache()->flexible('holocron.school.homeworks', [now()->addMinutes(15), now()->addYear()], fn () => $this->untis->homeworks(CarbonImmutable::today()->subDays(10), CarbonImmutable::today()->addDays(21)));
    }

    private function exams(): Collection
    {
        return cache()->flexible('holocron.school.exams', [now()->addMinutes(15), now()->addYear()], fn () => $this->untis->exams(CarbonImmutable::today(), CarbonImmutable::today()->addDays(21)));
    }
}
