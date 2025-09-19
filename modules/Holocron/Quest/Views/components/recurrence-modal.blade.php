<flux:modal name="recurrence-modal">
    <form wire:submit="saveRecurrence" class="space-y-4">
        <flux:select wire:model="recurrenceType" label="Wiederholungs-Typ">
            <option value="daily">Täglich</option>
            <option value="weekly">Wöchentlich</option>
            <option value="monthly">Monatlich</option>
        </flux:select>

        <flux:input wire:model="recurrenceValue" type="number" label="Jede x wiederholen" />

        <flux:date-picker wire:model="recurrenceEndsAt" label="Ended nach" />

        <div class="flex gap-x-2">
            <flux:button type="submit" variant="primary">Wiederholung speichern</flux:button>

            @if ($quest->recurrence)
                <flux:button wire:click="deleteRecurrence" variant="danger">Löschen</flux:button>
            @endif
        </div>
    </form>
</flux:modal>
