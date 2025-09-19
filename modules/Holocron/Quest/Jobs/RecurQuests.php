<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Holocron\Quest\Enums\QuestRecurrenceType;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

class RecurQuests implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $recurrences = QuestRecurrence::where('ends_at', '>', now())
            ->orWhereNull('ends_at')
            ->get();

        foreach ($recurrences as $recurrence) {
            if ($this->shouldRecur($recurrence)) {
                $this->recur($recurrence);
            }
        }
    }

    private function shouldRecur(QuestRecurrence $recurrence): bool
    {
        if ($recurrence->last_recurred_at === null) {
            return true;
        }

        return match ($recurrence->type) {
            QuestRecurrenceType::Daily => $recurrence->last_recurred_at->addDays($recurrence->value)->isPast(),
            QuestRecurrenceType::Weekly => $recurrence->last_recurred_at->addWeeks($recurrence->value)->isPast(),
            QuestRecurrenceType::Monthly => $recurrence->last_recurred_at->addMonths($recurrence->value)->isPast(),
        };
    }

    private function recur(QuestRecurrence $recurrence): void
    {
        $masterQuest = $recurrence->quest;

        $latestInstance = Quest::where('created_from_recurrence_id', $recurrence->id)
            ->latest()
            ->first();

        if ($latestInstance && ! $latestInstance->isCompleted()) {
            return;
        }

        Quest::create([
            'name' => $masterQuest->name,
            'description' => $masterQuest->description,
            'quest_id' => $masterQuest->quest_id,
            'images' => $masterQuest->images,
            'should_be_printed' => $masterQuest->should_be_printed,
            'created_from_recurrence_id' => $recurrence->id,
        ]);

        $recurrence->update(['last_recurred_at' => now()]);
    }
}
