<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Livewire\Items;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Category;
use Modules\Holocron\Gear\Models\Item;

#[Title('Artikel')]
class Index extends HolocronComponent
{
    #[Validate('required|string|min:3|max:255')]
    public string $name = '';

    #[Validate('integer')]
    public ?int $category_id = null;

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
        return view('holocron-gear::items.index', [
            'items' => Item::all(),
            'categories' => Category::pluck('name', 'id'),
            'availableProperties' => Property::cases(),
        ]);
    }
}
