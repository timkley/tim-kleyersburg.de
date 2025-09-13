<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\Intents;

use Illuminate\Support\Collection;
use Modules\Holocron\Quest\Models\Quest;

class ParentQuest implements IntentInterface
{
    public static function results(?string $query = null): Collection
    {
        if (empty($query)) {
            return collect();
        }

        return Quest::search($query)
            ->take(10)
            ->get()
            ->map(fn (Quest $quest) => new Intent(
                type: 'parent-quest',
                label: $quest->breadcrumb()->pluck('name')->implode(' > '),
                property: 'quest_id',
                value: $quest->id
            ));
    }
}
