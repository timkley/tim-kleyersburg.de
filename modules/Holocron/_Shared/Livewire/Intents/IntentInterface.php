<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\Intents;

use Illuminate\Support\Collection;

interface IntentInterface
{
    /**
     * @return Collection<int, Intent>
     */
    public static function results(?string $query = null): Collection;
}
