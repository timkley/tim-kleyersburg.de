<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Quest;

final readonly class CreateQuest
{
    public function handle(array $data): Quest
    {
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'min:1'],
            'quest_id' => ['nullable', 'integer', 'exists:quests,id'],
            'date' => ['nullable', 'date'],
            'daily' => ['nullable', 'boolean'],
            'is_note' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ])->validate();

        return Quest::create($validated);
    }
}
