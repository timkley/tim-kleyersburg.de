<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Livewire;

use Livewire\Attributes\Validate;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Journey;

trait WithJourneyCreation
{
    #[Validate('required|string|min:3|max:255')]
    public ?string $destination = null;

    #[Validate('required|date')]
    public ?string $starts_at = null;

    #[Validate('required|date')]
    public ?string $ends_at = null;

    /** @var array<Property> */
    public array $selectedProperties = [];

    public function toggleProperty(string $propertyValue): void
    {
        $property = Property::from($propertyValue);
        $key = array_search($property, $this->selectedProperties, true);

        if ($key !== false) {
            array_splice($this->selectedProperties, $key, 1);
        } else {
            $this->selectedProperties[] = $property;
        }
    }

    public function isPropertySelected(Property $property): bool
    {
        return in_array($property, $this->selectedProperties, true);
    }

    public function submit(): void
    {
        $validated = $this->validate();
        $validated['properties'] = collect($this->selectedProperties);

        $journey = Journey::create($validated);

        $this->generatePacklist($journey);

        $this->redirectRoute('holocron.gear.journeys.show', $journey->id);
    }
}
