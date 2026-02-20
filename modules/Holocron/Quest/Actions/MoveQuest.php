<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Quest;

final readonly class MoveQuest
{
    public function handle(Quest $quest, array $data): Quest
    {
        $validated = Validator::make($data, [
            'quest_id' => ['nullable', 'integer', 'exists:quests,id'],
        ])->validate();

        $quest->update(['quest_id' => $validated['quest_id']]);

        return $quest->refresh();
    }
}
