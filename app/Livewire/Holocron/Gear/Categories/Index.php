<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear\Categories;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Gear\Category;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Kategorien')]
class Index extends HolocronComponent
{
    #[Validate('required|string|min:3|max:255')]
    public string $name = '';

    public function submit(): void
    {
        $validated = $this->validate();

        Category::create($validated);

        $this->reset();
    }

    public function delete(int $id): void
    {
        Category::destroy($id);
    }

    public function render(): View
    {
        return view('holocron.gear.categories.index', [
            'categories' => Category::all(),
        ]);
    }
}
