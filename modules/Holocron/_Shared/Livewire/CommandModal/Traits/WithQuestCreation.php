<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\CommandModal\Traits;

use Modules\Holocron\Quest\Models\Quest;

trait WithQuestCreation
{
    use WithIntents;

    public string $name = '';

    public function createQuest(bool $andNew = false): void
    {
        $quest = Quest::create([
            'name' => $this->name,
            ...$this->payload,
        ]);

        if ($andNew) {
            $this->name = '';
            $this->payload = [];
            $this->labels = [];

            $this->dispatch('quest:created');

            return;
        }

        $this->redirect(route('holocron.quests.show', $quest));
    }
}
