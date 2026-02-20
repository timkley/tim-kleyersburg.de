<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Quest;

final readonly class UpdateQuest
{
    public function handle(Quest $quest, array $data): Quest
    {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'string', 'min:1'],
            'description' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'daily' => ['sometimes', 'boolean'],
            'is_note' => ['sometimes', 'boolean'],
        ])->validate();

        $quest->update($validated);

        return $quest->refresh();
    }
}
