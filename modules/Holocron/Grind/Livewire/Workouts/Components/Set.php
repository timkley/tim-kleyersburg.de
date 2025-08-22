<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Workouts\Components;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\Set as SetModel;

#[Title('Workouts')]
class Set extends HolocronComponent
{
    public SetModel $set;

    public int $minReps = 0;

    public int $maxReps = 0;

    public int $iteration;

    #[Validate('required|numeric')]
    public float $weight;

    #[Validate('required|int')]
    public int $reps;

    /**
     * @var string[]
     */
    protected $listeners = [
        'set:started' => '$refresh',
    ];

    public function updated(string $property, mixed $value): void
    {
        $this->validateOnly($property);

        $this->set->update([
            $property => $value,
        ]);
    }

    public function start(): void
    {
        $this->set->siblings()->whereNotNull('started_at')->get()->each->update(['finished_at' => now()]);

        $this->set->update([
            'started_at' => now(),
        ]);

        $this->dispatch('set:started');
    }

    public function finish(): void
    {
        $this->set->update([
            'finished_at' => now(),
        ]);

        $this->dispatch('set:stopped');
    }

    public function mount(): void
    {
        $this->weight = $this->set->weight;
        $this->reps = $this->set->reps;
    }

    public function render(): View
    {
        return view('holocron-grind::workouts.components.set');
    }
}
