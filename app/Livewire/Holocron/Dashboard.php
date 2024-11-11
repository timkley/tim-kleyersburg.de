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

        return [
            new DashboardCard(
                'Schule Emi',
                route('holocron.school.index'),
                'academic-cap',
                [
                    $homeworks,
                    $exams,
                ]
            ),
            new DashboardCard(
                'Pulse',
                '/holocron/pulse',
                'chart-bar',
            ),
        ];
    }
}
