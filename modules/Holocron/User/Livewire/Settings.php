<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\User\Models\User;

#[Title('Einstellungen')]
class Settings extends HolocronComponent
{
    public float $weight;

    public function updatedWeight(mixed $value): void
    {
        User::tim()->settings()->update(['weight' => $value]);
    }

    public function mount(): void
    {
        $settings = User::tim()->settings;
        $this->weight = $settings->weight;
    }

    public function render(): View
    {
        return view('holocron-user::settings');
    }
}
