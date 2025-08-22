<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Livewire\Categories;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Gear\Models\Category;

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
        return view('holocron-gear::categories.index', [
            'categories' => Category::all(),
        ]);
    }
}
