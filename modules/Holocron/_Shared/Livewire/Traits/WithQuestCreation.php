<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\Traits;

use Modules\Holocron\Quest\Models\Quest;

trait WithQuestCreation
{
    public string $name = '';

    public function createQuest(bool $andNew = false): void
    {
        $quest = Quest::create([
            'name' => $this->name,
        ]);

        if ($andNew) {
            $this->name = '';

            return;
        }

        $this->dispatch('quest:created');
        $this->redirect(route('holocron.quests.show', $quest));
    }
}
