<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

final readonly class SaveRecurrence
{
    public function handle(Quest $quest, array $data): QuestRecurrence
    {
        $validated = Validator::make($data, [
            'every_x_days' => ['required', 'integer', 'min:1'],
            'recurrence_type' => ['required', 'string', 'in:'.QuestRecurrence::TYPE_RECURRENCE_BASED.','.QuestRecurrence::TYPE_COMPLETION_BASED],
            'ends_at' => ['nullable', 'date'],
        ])->validate();

        return $quest->recurrence()->updateOrCreate([], [
            'every_x_days' => $validated['every_x_days'],
            'recurrence_type' => $validated['recurrence_type'],
            'last_recurred_at' => today(),
            'ends_at' => $validated['ends_at'] ?? null,
        ]);
    }
}
