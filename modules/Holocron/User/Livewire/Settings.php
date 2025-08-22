<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Holocron\User\Models\User;

#[Layout('components.layouts.holocron')]
#[Title('Einstellungen')]
class Settings extends Component
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
