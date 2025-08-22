<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Livewire;

use Livewire\Attributes\Validate;
use Modules\Holocron\Gear\Models\Journey;

trait WithJourneyCreation
{
    #[Validate('required|string|min:3|max:255')]
    public ?string $destination = null;

    #[Validate('required|date')]
    public ?string $starts_at = null;

    #[Validate('required|date')]
    public ?string $ends_at = null;

    /** @var array|string[] */
    #[Validate('required|array')]
    public array $participants = ['adult'];

    public function toggleKid(): void
    {
        if (in_array('kid', $this->participants)) {
            $this->participants = array_diff($this->participants, ['kid']);
        } else {
            $this->participants[] = 'kid';
        }
    }

    public function submit(): void
    {
        $validated = $this->validate();

        $journey = Journey::create($validated);

        $this->generatePacklist($journey);

        $this->redirectRoute('holocron.gear.journeys.show', $journey->id);
    }
}
