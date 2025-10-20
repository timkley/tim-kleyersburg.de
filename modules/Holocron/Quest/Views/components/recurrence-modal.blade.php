<flux:modal name="recurrence-modal">
    <form wire:submit="saveRecurrence" class="space-y-4">
        <flux:radio.group 
            wire:model.live="recurrenceType" 
            label="Wiederholungstyp" 
            variant="segmented"
            description="Wähle, ob neue Aufgaben basierend auf der letzten Wiederholung oder der letzten Fertigstellung erstellt werden"
        >
            <flux:radio value="recurrence_based" label="Seit letzter Wiederholung" />
            <flux:radio value="completion_based" label="Nach letzter Fertigstellung" />
        </flux:radio.group>

        <div class="space-y-2">
            <flux:label>Häufige Intervalle</flux:label>
            <flux:button.group>
                <flux:button
                    x-on:click="$wire.recurrenceDays = 1"
                    size="sm"
                >Täglich</flux:button>
                <flux:button
                    x-on:click="$wire.recurrenceDays = 7"
                    size="sm"
                >Wöchentlich</flux:button>
                <flux:button
                    x-on:click="$wire.recurrenceDays = 30"
                    size="sm"
                >Monatlich</flux:button>
            </flux:button.group>
        </div>

        <flux:input wire:model="recurrenceDays" type="number" label="Alle X Tage wiederholen" min="1" />

        <flux:date-picker wire:model="recurrenceEndsAt" label="Ended nach" />

        <div class="flex gap-x-2">
            <flux:button type="submit" variant="primary">Wiederholung speichern</flux:button>

            @if ($quest->recurrence)
                <flux:button wire:click="deleteRecurrence" variant="danger">Löschen</flux:button>
            @endif
        </div>
    </form>
</flux:modal>
