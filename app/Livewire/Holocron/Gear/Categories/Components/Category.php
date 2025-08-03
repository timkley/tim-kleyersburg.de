<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear\Categories\Components;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Gear\Category as CategoryModel;
use Illuminate\View\View;

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
        return view('holocron.gear.categories.components.category');
    }
}
