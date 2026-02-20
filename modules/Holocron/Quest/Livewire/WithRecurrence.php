<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Modules\Holocron\Quest\Actions\DeleteRecurrence as DeleteRecurrenceAction;
use Modules\Holocron\Quest\Actions\SaveRecurrence;
use Modules\Holocron\Quest\Models\QuestRecurrence;

trait WithRecurrence
{
    public int $recurrenceDays = 1;

    public string $recurrenceType = QuestRecurrence::TYPE_RECURRENCE_BASED;

    public ?string $recurrenceEndsAt = null;

    public function mountWithRecurrence(): void
    {
        if ($this->quest->recurrence) {
            $this->recurrenceDays = $this->quest->recurrence->every_x_days;
            $this->recurrenceType = $this->quest->recurrence->recurrence_type;
            $this->recurrenceEndsAt = $this->quest->recurrence->ends_at?->format('Y-m-d');
        }
    }

    public function saveRecurrence(): void
    {
        (new SaveRecurrence)->handle($this->quest, [
            'every_x_days' => $this->recurrenceDays,
            'recurrence_type' => $this->recurrenceType,
            'ends_at' => $this->recurrenceEndsAt,
        ]);
    }

    public function deleteRecurrence(): void
    {
        (new DeleteRecurrenceAction)->handle($this->quest);
        $this->reset(['recurrenceDays', 'recurrenceType', 'recurrenceEndsAt']);
    }
}
