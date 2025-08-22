<flux:modal name="reminder-modal">
    <form wire:submit="updateReminder" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input label="Datum" type="date" wire:model="reminderDate" />
            <flux:input label="Zeit" type="time" wire:model="reminderTime" />
        </div>

        <div class="flex justify-end gap-2">
            <flux:button type="submit" variant="primary">
                {{ $editingReminderId ? 'Aktualisieren' : 'Erstellen' }}
            </flux:button>
        </div>
    </form>

    @if($this->activeReminders->isNotEmpty() && !$editingReminderId)
        <div class="mt-6">
            <flux:heading size="sm">Aktive Erinnerungen</flux:heading>
            <div class="space-y-2 mt-2">
                @foreach($this->activeReminders as $reminder)
                    <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-800 rounded">
                        <flux:text>{{ $reminder->remind_at->format('M d, Y H:i') }}</flux:text>
                        <div class="flex gap-2">
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="pencil"
                                wire:click="editReminder({{ $reminder->id }})"
                            ></flux:button>
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="trash"
                                wire:click="deleteReminder({{ $reminder->id }})"
                                wire:confirm="Willst du die Erinnerung wirklich löschen?"
                            ></flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</flux:modal>
