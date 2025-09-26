<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\Intents;

use Illuminate\Support\Collection;

class Date implements IntentInterface
{
    public static function results(?string $query = null): Collection
    {
        return collect(
            [
                new Intent(
                    type: 'date',
                    label: 'Heute',
                    property: 'date',
                    value: today()->toDateString(),
                ),
                new Intent(
                    type: 'date',
                    label: 'Morgen',
                    property: 'date',
                    value: today()->addDay()->toDateString(),
                ),
                new Intent(
                    type: 'date',
                    label: 'Übermorgen',
                    property: 'date',
                    value: today()->addDays(2)->toDateString(),
                ),
            ]
        );
    }
}
