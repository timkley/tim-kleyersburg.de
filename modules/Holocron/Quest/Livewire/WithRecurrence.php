<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

trait WithRecurrence
{
    public int $recurrenceDays = 1;

    public ?string $recurrenceEndsAt = null;

    public function mountWithRecurrence(): void
    {
        if ($this->quest->recurrence) {
            $this->recurrenceDays = $this->quest->recurrence->every_x_days;
            $this->recurrenceEndsAt = $this->quest->recurrence->ends_at?->format('Y-m-d');
        }
    }

    public function saveRecurrence(): void
    {
        $this->validate([
            'recurrenceDays' => 'required|integer|min:1',
            'recurrenceEndsAt' => 'nullable|date',
        ]);

        $this->quest->recurrence()->updateOrCreate([], [
            'every_x_days' => $this->recurrenceDays,
            'last_recurred_at' => today(),
            'ends_at' => $this->recurrenceEndsAt,
        ]);
    }

    public function deleteRecurrence(): void
    {
        $this->quest->recurrence()->delete();
        $this->reset(['recurrenceDays', 'recurrenceEndsAt']);
    }
}
