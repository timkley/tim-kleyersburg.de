<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Modules\Holocron\Quest\Actions\DeleteReminder as DeleteReminderAction;
use Modules\Holocron\Quest\Actions\SaveReminder;
use Modules\Holocron\Quest\Models\Reminder;

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
        $this->reminderDate = now()->format('Y-m-d');
        $this->reminderTime = now()->addHour()->format('H:i');
    }

    public function updateReminder(): void
    {
        $this->validateOnly('reminderDate');
        $this->validateOnly('reminderTime');

        (new SaveReminder)->handle($this->quest, [
            'id' => $this->editingReminderId,
            'remind_at' => "{$this->reminderDate} {$this->reminderTime}",
            'type' => 'once',
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
        (new DeleteReminderAction)->handle(Reminder::findOrFail($id));

        if ($this->editingReminderId === $id) {
            $this->reset(['reminderDate', 'reminderTime', 'editingReminderId']);
        }
    }
}
