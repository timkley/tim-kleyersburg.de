<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\Traits;

use Illuminate\Support\Collection;
use Modules\Holocron\_Shared\Livewire\Intents\Intent;
use Modules\Holocron\_Shared\Livewire\Intents\ParentQuest;

trait WithIntents
{
    /** @var Collection<int, Intent> */
    public Collection $results;

    /** @var array<string, mixed> */
    public array $payload = [];

    /** @var array<string, string> */
    public array $labels = [];

    /** @var array<int, string> */
    protected array $intents = [
        ParentQuest::class,
    ];

    public function loadIntents(): void
    {
        $query = str($this->name)->afterLast('/')->toString();

        if (! $query) {
            return;
        }

        $results = collect();

        foreach ($this->intents as $intent) {
            $results->push(...$intent::results($query));
        }

        $this->results = $results;
    }

    /** @param array<string, mixed> $intent */
    public function processIntent(array $intent): void
    {
        $intent = Intent::fromLivewire($intent);
        $this->payload[$intent->property] = $intent->value;
        $this->labels[$intent->type] = $intent->label;
        $this->name = str($this->name)->beforeLast('/')->toString();
        $this->hasIntent = false;
        $this->results = collect();
    }
}
