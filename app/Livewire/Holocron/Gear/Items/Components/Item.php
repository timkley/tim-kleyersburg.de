<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear\Items\Components;

use App\Enums\Holocron\Gear\Property;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Gear\Category;
use App\Models\Holocron\Gear\Item as ItemModel;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Item extends HolocronComponent
{
    public ItemModel $item;

    public string $name = '';

    public string|int $category_id = '';

    /**
     * @var ?Collection<int,Property>
     */
    public ?Collection $properties;

    public int $quantity = 0;

    public float $quantity_per_day = 0;

    public function updated(string $property, mixed $value): void
    {
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
        return view('holocron.gear.items.components.item', [
            'categories' => Category::pluck('name', 'id'),
            'availableProperties' => Property::cases(),
        ]);
    }
}
