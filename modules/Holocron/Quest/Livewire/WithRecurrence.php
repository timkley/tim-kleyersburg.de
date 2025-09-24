<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

trait WithRecurrence
{
    public string $recurrenceType = '';

    public int $recurrenceValue = 1;

    public ?string $recurrenceEndsAt = null;

    public function mountWithRecurrence(): void
    {
        if ($this->quest->recurrence) {
            $this->recurrenceType = $this->quest->recurrence->type->value;
            $this->recurrenceValue = $this->quest->recurrence->value;
            $this->recurrenceEndsAt = $this->quest->recurrence->ends_at?->format('Y-m-d');
        }
    }

    public function saveRecurrence(): void
    {
        $this->validate([
            'recurrenceType' => 'required|in:daily,weekly,monthly',
            'recurrenceValue' => 'required|integer|min:1',
            'recurrenceEndsAt' => 'nullable|date',
        ]);

        $this->quest->recurrence()->updateOrCreate([], [
            'type' => $this->recurrenceType,
            'value' => $this->recurrenceValue,
            'last_recurred_at' => today(),
            'ends_at' => $this->recurrenceEndsAt,
        ]);
    }

    public function deleteRecurrence(): void
    {
        $this->quest->recurrence()->delete();
        $this->reset(['recurrenceType', 'recurrenceValue', 'recurrenceEndsAt']);
    }
}
