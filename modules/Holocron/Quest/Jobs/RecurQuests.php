<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $recurrences = QuestRecurrence::with(['quest'])
            ->where(function ($query) {
                $query->where('ends_at', '>', now()->startOfDay())
                    ->orWhereNull('ends_at');
            })
            ->get();

        foreach ($recurrences as $recurrence) {
            if ($this->shouldRecur($recurrence)) {
                $this->recur($recurrence);
            }
        }
    }

    private function shouldRecur(QuestRecurrence $recurrence): bool
    {
        if ($this->hasOpenChild($recurrence)) {
            return false;
        }

        $dueAt = $this->calculateDueDate($recurrence);

        if ($dueAt === null) {
            return false;
        }

        return $dueAt->startOfDay()->lte(now()->startOfDay());
    }

    private function recur(QuestRecurrence $recurrence): void
    {
        $masterQuest = $recurrence->quest;

        DB::transaction(function () use ($recurrence, $masterQuest) {
            Quest::query()->create([
                'name' => $masterQuest->name,
                'description' => $masterQuest->description,
                'date' => today(),
                'quest_id' => $masterQuest->quest_id,
                'attachments' => $masterQuest->attachments,
                'should_be_printed' => $masterQuest->should_be_printed,
                'created_from_recurrence_id' => $recurrence->id,
            ]);

            $recurrence->update(['last_recurred_at' => now()]);
        });
    }

    private function hasOpenChild(QuestRecurrence $recurrence): bool
    {
        return Quest::where('created_from_recurrence_id', $recurrence->id)
            ->whereNull('completed_at')
            ->exists();
    }

    private function calculateDueDate(QuestRecurrence $recurrence): ?Carbon
    {
        return match ($recurrence->recurrence_type) {
            QuestRecurrence::TYPE_RECURRENCE_BASED => $this->dueAtForRecurrenceBased($recurrence),
            QuestRecurrence::TYPE_COMPLETION_BASED => $this->dueAtForCompletionBased($recurrence),
            default => null,
        };
    }

    private function dueAtForRecurrenceBased(QuestRecurrence $recurrence): Carbon
    {
        $baseDate = $recurrence->last_recurred_at ?? $recurrence->created_at;

        return Carbon::parse($baseDate)->addDays($recurrence->every_x_days);
    }

    private function dueAtForCompletionBased(QuestRecurrence $recurrence): ?Carbon
    {
        $referenceCompletionAt = $this->referenceCompletionAt($recurrence);

        if ($referenceCompletionAt === null) {
            return null;
        }

        return Carbon::parse($referenceCompletionAt)->addDays($recurrence->every_x_days);
    }

    private function referenceCompletionAt(QuestRecurrence $recurrence): ?Carbon
    {
        $latestCompletedChild = Quest::where('created_from_recurrence_id', $recurrence->id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->first();

        if ($latestCompletedChild) {
            return Carbon::parse($latestCompletedChild->completed_at);
        }

        $masterQuest = $recurrence->quest;
        if ($masterQuest->completed_at !== null) {
            return Carbon::parse($masterQuest->completed_at);
        }

        return null;
    }
}
