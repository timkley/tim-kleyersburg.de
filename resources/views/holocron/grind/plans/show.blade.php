<div>
    <flux:heading size="xl">{{ $plan->name }}</flux:heading>

    <div class="space-y-1">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Übung</flux:table.column>
                <flux:table.column>Sets</flux:table.column>
                <flux:table.column>Min. Reps</flux:table.column>
                <flux:table.column>Max. Reps</flux:table.column>
                <flux:table.column>Order</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($plan->exercises as $exercise)
                    <livewire:holocron.grind.plans.components.exercise :$exercise :key="$exercise->id" />
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    <form wire:submit="addExercise" class="space-y-2 mt-6 max-w-lg">
        <flux:heading>Übung hinzufügen</flux:heading>
        <flux:select wire:model="exerciseId" variant="listbox" placeholder="Übung">
            @foreach($availableExercises as $availableExercise)
                <flux:select.option :disabled="$plan->exercises->contains($availableExercise)" value="{{ $availableExercise->id }}">{{ $availableExercise->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <flux:input class="col-span-2 sm:col-span-1" wire:model="sets" placeholder="Sets" icon="clipboard-document-check" />
            <flux:input wire:model="minReps" placeholder="Min. Wdh." icon="arrow-down" />
            <flux:input wire:model="maxReps" placeholder="Max. Wdh." icon="arrow-up" />
        </div>
        <flux:input wire:model="order" placeholder="Reihenfolge" value="1" icon="numbered-list" />

        <flux:button type="submit" variant="primary">Übung hinzufügen</flux:button>
    </form>
</div>
