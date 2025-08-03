<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear\Items;

use App\Enums\Holocron\Gear\Property;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Gear\Item;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Artikel')]
class Index extends HolocronComponent
{
    #[Validate('required|string|min:3|max:255')]
    public string $name = '';

    /** @var array|string[] */
    #[Validate('array')]
    public array $properties = [];

    public function submit(): void
    {
        $validated = $this->validate();

        Item::create($validated);

        $this->reset();
    }

    public function delete(int $id): void
    {
        Item::destroy($id);
    }

    public function render(): View
    {
        return view('holocron.gear.items.index', [
            'items' => Item::all(),
            'availableProperties' => Property::cases(),
        ]);
    }
}
