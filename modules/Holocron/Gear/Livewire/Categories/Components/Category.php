<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Livewire\Categories\Components;

use Illuminate\View\View;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Gear\Models\Category as CategoryModel;

class Category extends HolocronComponent
{
    public CategoryModel $category;

    public string $name = '';

    public function updated(string $property, mixed $value): void
    {
        $this->category->update([
            $property => $value,
        ]);
    }

    public function mount(): void
    {
        $this->name = $this->category->name;
    }

    public function render(): View
    {
        return view('holocron-gear::categories.components.category');
    }
}
