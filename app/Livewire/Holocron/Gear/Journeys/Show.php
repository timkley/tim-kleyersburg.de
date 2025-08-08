<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear\Journeys;

use App\Livewire\Holocron\Gear\WithPacklistGeneration;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Gear\Journey;
use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Reise')]
class Show extends HolocronComponent
{
    use WithPacklistGeneration;

    public Journey $journey;

    /**
     * @var string[]
     */
    protected $listeners = [
        'journey-item:deleted' => '$refresh',
    ];

    public function render(): View
    {
        return view('holocron.gear.journeys.show', [
            'groups' => $this->journey->journeyItems->groupBy('item.category_id'),
        ]);
    }
}
