<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;

final readonly class SaveReminder
{
    public function handle(Quest $quest, array $data): Reminder
    {
        $validated = Validator::make($data, [
            'id' => ['nullable', 'integer', 'exists:quest_reminders,id'],
            'remind_at' => ['required', 'date'],
            'type' => ['required', 'string', 'in:once,cron'],
            'recurrence_pattern' => ['nullable', 'string'],
        ])->validate();

        return Reminder::query()->updateOrCreate(
            ['id' => $validated['id'] ?? null],
            [
                'quest_id' => $quest->id,
                'remind_at' => $validated['remind_at'],
                'type' => $validated['type'],
                'recurrence_pattern' => $validated['recurrence_pattern'] ?? null,
                'last_processed_at' => null,
            ],
        );
    }
}
