<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\HealthData as HealthDataModel;

#[Title('Health Data')]
class HealthData extends HolocronComponent
{
    use WithPagination;

    public function render(): View
    {
        return view('holocron-grind::health-data', [
            'entries' => HealthDataModel::query()->latest('date')->latest('id')->paginate(100),
            'count' => HealthDataModel::query()->count(),
        ]);
    }
}
