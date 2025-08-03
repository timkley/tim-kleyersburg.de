<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear;

use App\Models\Holocron\Gear\Journey;
use App\Services\Weather;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

trait WithJourneyCreation
{
    #[Validate('required|string|min:3|max:255')]
    public string $destination;

    #[Validate('required|date')]
    public string $starts_at;

    #[Validate('required|date')]
    public string $ends_at;

    /** @var array|string[] */
    #[Validate('required|array')]
    public array $participants = ['adult'];

    #[Computed]
    public function days(): float|int
    {
        if (! empty($this->starts_at) && ! empty($this->ends_at)) {
            $starts_at = CarbonImmutable::parse($this->starts_at);
            $ends_at = CarbonImmutable::parse($this->ends_at);

            return $starts_at->diffInDays($ends_at) + 1;
        }

        return 0;
    }

    #[Computed]
    public function conditionIcon(): string
    {
        if (! empty($this->destination)) {
            $forecast = Weather::forecast($this->destination, 4);

            return $forecast->conditionIcon;
        }

        return '';
    }

    public function submit(): void
    {
        $validated = $this->validate();

        $journey = Journey::create($validated);

        $this->redirectRoute('holocron.gear.journeys.show', $journey->id);
    }
}
