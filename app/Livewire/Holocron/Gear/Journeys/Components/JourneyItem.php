<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear\Journeys\Components;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Gear\JourneyItem as JourneyItemModel;
use Illuminate\View\View;

class JourneyItem extends HolocronComponent
{
    public JourneyItemModel $journeyItem;

    public string $name = '';

    public bool $packed_for_departure = false;

    public bool $packed_for_return = false;

    public function updated(string $property, mixed $value): void
    {
        $this->journeyItem->update([
            $property => $value,
        ]);
    }

    public function delete(): void
    {
        $this->journeyItem->delete();

        $this->dispatch('journey-item:deleted');
    }

    public function mount(): void
    {
        $this->name = $this->journeyItem->item->name;
        $this->packed_for_departure = $this->journeyItem->packed_for_departure;
        $this->packed_for_return = $this->journeyItem->packed_for_return;
    }

    public function render(): View
    {
        return view('holocron.gear.journeys.components.journey-item');
    }
}
