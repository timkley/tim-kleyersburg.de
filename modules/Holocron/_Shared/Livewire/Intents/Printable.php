<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\Intents;

use Illuminate\Support\Collection;

class Printable implements IntentInterface
{
    public static function results(?string $query = null): Collection
    {
        return collect(
            [
                new Intent(
                    type: 'printable',
                    label: 'Ausdrucken',
                    property: 'should_be_printed',
                    value: true
                ),
            ]
        );
    }
}
