<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Nutrition;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\BodyMeasurement;

#[Title('Körper')]
class BodyMeasurements extends HolocronComponent
{
    public string $date;

    public ?float $weight = null;

    public ?float $bodyFat = null;

    public ?float $muscleMass = null;

    public ?int $visceralFat = null;

    public ?float $bmi = null;

    public ?float $bodyWater = null;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function addMeasurement(): void
    {
        $this->validate([
            'date' => 'required|date',
            'weight' => 'required|numeric|min:0',
            'bodyFat' => 'nullable|numeric|min:0|max:100',
            'muscleMass' => 'nullable|numeric|min:0',
            'visceralFat' => 'nullable|integer|min:0',
            'bmi' => 'nullable|numeric|min:0',
            'bodyWater' => 'nullable|numeric|min:0|max:100',
        ]);

        BodyMeasurement::query()->updateOrCreate(
            ['date' => $this->date],
            [
                'weight' => $this->weight,
                'body_fat' => $this->bodyFat,
                'muscle_mass' => $this->muscleMass,
                'visceral_fat' => $this->visceralFat,
                'bmi' => $this->bmi,
                'body_water' => $this->bodyWater,
            ],
        );

        $this->reset('weight', 'bodyFat', 'muscleMass', 'visceralFat', 'bmi', 'bodyWater');
    }

    public function render(): View
    {
        $measurements = BodyMeasurement::query()
            ->orderByDesc('date')
            ->get();

        $chartData = $measurements->sortBy('date')->values()->map(fn (BodyMeasurement $m) => [
            'date' => $m->date->format('d.m.Y'),
            'weight' => (float) $m->weight,
            'muscle_mass' => $m->muscle_mass !== null ? (float) $m->muscle_mass : null,
        ])->all();

        return view('holocron-grind::nutrition.body-measurements', [
            'measurements' => $measurements,
            'chartData' => $chartData,
        ]);
    }
}
