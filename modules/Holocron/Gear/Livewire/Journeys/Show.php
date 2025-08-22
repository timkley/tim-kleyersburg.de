<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Livewire\Journeys;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Gear\Livewire\WithPacklistGeneration;
use Modules\Holocron\Gear\Models\Journey;

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
        return view('holocron-gear::journeys.show', [
            'groups' => $this->journey->journeyItems->groupBy('item.category_id'),
        ]);
    }
}
