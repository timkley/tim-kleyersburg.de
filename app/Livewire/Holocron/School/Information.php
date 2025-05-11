<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\School;

use App\Data\Untis\Exam;
use App\Data\Untis\Homework;
use App\Data\Untis\Lesson;
use App\Data\Untis\News;
use App\Livewire\Holocron\HolocronComponent;
use App\Services\Untis;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Information extends HolocronComponent
{
    private readonly Untis $untis;

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

    /**
     * @return Collection<int, News>
     */
    private function news(): Collection
    {
        return cache()->flexible('holocron.school.news', [now()->addMinutes(15), now()->addYear()], fn (): Collection => $this->untis->news());
    }

    /**
     * @return Collection<int, Lesson>
     */
    private function timetable(): Collection
    {
        return cache()->flexible('holocron.school.timetable', [now()->addMinutes(15), now()->addYear()], fn (): Collection => $this->untis->timetable(CarbonImmutable::today(), CarbonImmutable::today()->addDays(14)));
    }

    /**
     * @return Collection<int, Homework>
     */
    private function homeworks(): Collection
    {
        return cache()->flexible('holocron.school.homeworks', [now()->addMinutes(15), now()->addYear()], fn (): Collection => $this->untis->homeworks(CarbonImmutable::today()->subDays(10), CarbonImmutable::today()->addDays(21)));
    }

    /**
     * @return Collection<int, Exam>
     */
    private function exams(): Collection
    {
        return cache()->flexible('holocron.school.exams', [now()->addMinutes(15), now()->addYear()], fn (): Collection => $this->untis->exams(CarbonImmutable::today(), CarbonImmutable::today()->addDays(21)));
    }
}
