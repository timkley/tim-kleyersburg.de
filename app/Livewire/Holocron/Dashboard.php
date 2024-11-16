<?php

namespace App\Livewire\Holocron;

use App\Data\Holocron\DashboardCard;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.holocron')]
class Dashboard extends Component
{
    public function render()
    {
        return view('holocron.dashboard', [
            'cards' => $this->cards(),
        ]);
    }

    private function cards()
    {
        $homeworks = cache('holocron.school.homeworks');
        $homeworks = (is_null($homeworks) ? 'n/a' : count($homeworks)).' Hausaufgaben';

        $exams = cache('holocron.school.exams');
        $exams = (is_null($exams) ? 'n/a' : count($exams)).' Klassenarbeiten';

        $cards = [];

        $cards[] = new DashboardCard(
            'Schule Emi',
            route('holocron.school.index'),
            'academic-cap',
            [
                $homeworks,
                $exams,
            ]
        );

        $cards[] = new DashboardCard(
            'Schule Emi Vokabeln',
            route('holocron.school.vocabulary.overview'),
            'academic-cap',
        );

        if (auth()->user()->email === 'timkley@gmail.com') {
            $cards[] = new DashboardCard(
                'Pulse',
                '/holocron/pulse',
                'chart-bar',
            );
        }

        return $cards;
    }
}
