<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Livewire\Items\Components;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Category;
use Modules\Holocron\Gear\Models\Item as ItemModel;

class Item extends HolocronComponent
{
    public ItemModel $item;

    #[Validate('required')]
    public string $name = '';

    #[Validate('required|exists:categories,id')]
    public string|int $category_id = '';

    /**
     * @var ?Collection<int,Property>
     */
    public ?Collection $properties;

    #[Validate('required|int')]
    public int $quantity = 0;

    #[Validate('required|numeric')]
    public float $quantity_per_day = 0;

    public function updated(string $property, mixed $value): void
    {
        $this->validateOnly($property);

        if (str_starts_with($property, 'properties')) {
            $this->item->update([
                'properties' => $this->properties,
            ]);

            return;
        }
        $this->item->update([
            $property => $value,
        ]);
    }

    public function mount(): void
    {
        $this->name = $this->item->name;
        $this->category_id = $this->item->category_id ?? '';
        $this->properties = $this->item->properties;
        $this->quantity = $this->item->quantity;
        $this->quantity_per_day = $this->item->quantity_per_day;
    }

    public function render(): View
    {
        return view('holocron-gear::items.components.item', [
            'categories' => Category::pluck('name', 'id'),
            'availableProperties' => Property::cases(),
        ]);
    }
}
