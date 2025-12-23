<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Livewire;

use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Gear\Models\Journey;

#[Title('Gear')]
class Index extends HolocronComponent
{
    use WithJourneyCreation, WithPacklistGeneration;

    public function delete(int $id): void
    {
        $journey = Journey::findOrFail($id);

        // Manually delete all related JourneyItems (no cascade constraint)
        $journey->journeyItems()->delete();

        // Delete the journey
        $journey->delete();

        Flux::toast('Reise gelöscht');
    }

    public function render(): View
    {
        return view('holocron-gear::index', [
            'journeys' => Journey::query()->where('ends_at', '>=', today()->toDateString())->get(),
        ]);
    }
}
