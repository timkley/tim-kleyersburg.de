<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear\Items\Components;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Gear\Item as ItemModel;
use Illuminate\View\View;

class Item extends HolocronComponent
{
    public ItemModel $item;

    public string $name = '';

    public function updated(string $property, mixed $value): void
    {
        $this->item->update([
            $property => $value,
        ]);
    }

    public function mount(): void
    {
        $this->name = $this->item->name;
    }

    public function render(): View
    {
        return view('holocron.gear.items.components.item');
    }
}
