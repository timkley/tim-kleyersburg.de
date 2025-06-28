<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Enums\Holocron\ReminderType;
use App\Models\Holocron\Quest\Reminder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

trait WithReminders
{
    #[Validate('required|date')]
    public string $reminderDate = '';

    #[Validate('required|date_format:H:i')]
    public string $reminderTime = '';

    public ?int $editingReminderId = null;

    /**
     * @return Collection<int, Reminder>
     */
    #[Computed]
    public function activeReminders(): Collection
    {
        return $this->quest->reminders()
            ->where(function (Builder $query) {
                $query->whereNull('last_processed_at')
                    ->orWhereColumn('last_processed_at', '<', 'remind_at');
            })
            ->orderBy('remind_at')
            ->get();
    }

    public function mountWithReminders(): void
    {
        // Set default values for the reminder form
        $this->reminderDate = now()->format('Y-m-d');
        $this->reminderTime = now()->addHour()->format('H:i');
    }

    public function updateReminder(): void
    {
        $this->validateOnly('reminderDate');
        $this->validateOnly('reminderTime');

        $remindAt = Carbon::parse("{$this->reminderDate} {$this->reminderTime}");

        Reminder::query()->updateOrCreate(
            [
                'id' => $this->editingReminderId,
            ],
            [
                'quest_id' => $this->quest->id,
                'remind_at' => $remindAt,
                'type' => ReminderType::Once,
                'last_processed_at' => null,
            ]);

        $this->reset(['reminderDate', 'reminderTime', 'editingReminderId']);
    }

    public function editReminder(int $id): void
    {
        $reminder = Reminder::findOrFail($id);

        $this->editingReminderId = $id;
        $this->reminderDate = $reminder->remind_at->format('Y-m-d');
        $this->reminderTime = $reminder->remind_at->format('H:i');
    }

    public function deleteReminder(int $id): void
    {
        Reminder::destroy($id);

        if ($this->editingReminderId === $id) {
            $this->reset(['reminderDate', 'reminderTime', 'editingReminderId']);
        }
    }
}
