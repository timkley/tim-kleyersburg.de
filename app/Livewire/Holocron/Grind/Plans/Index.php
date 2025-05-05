<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Plans;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Plan;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Pläne')]
class Index extends HolocronComponent
{
    #[Validate('required|min:3|max:255')]
    public ?string $name = null;

    public function submit(): void
    {
        $validated = $this->validate();

        Plan::create($validated);

        $this->reset('name');
    }

    public function delete(int $id): void
    {
        Plan::destroy($id);

        Flux::toast('Plan gelöscht');
    }

    public function render(): View
    {
        return view('holocron.grind.plans.index', [
            'plans' => Plan::all(),
        ]);
    }
}
