<flux:table.row>
    <flux:table.cell> {{ $exercise->name }} </flux:table.cell>
    <flux:table.cell>
        <flux:input.group>
            <flux:input
                class="min-w-12"
                wire:model.lazy="sets"
                wire:dirty.class="bg-yellow-50 dark:bg-yellow-900"
                inputmode="numeric"
            ></flux:input>
            <flux:input.group.suffix>Sets</flux:input.group.suffix>
        </flux:input.group>
    </flux:table.cell>
    <flux:table.cell>
        <flux:input.group>
            <flux:input
                class="min-w-12"
                wire:model.lazy="min_reps"
                wire:dirty.class="bg-yellow-50 dark:bg-yellow-900"
                inputmode="numeric"
            ></flux:input>
            <flux:input.group.suffix>Wdh.</flux:input.group.suffix>
        </flux:input.group>
    </flux:table.cell>
    <flux:table.cell>
        <flux:input.group>
            <flux:input
                class="min-w-12"
                wire:model.lazy="max_reps"
                wire:dirty.class="bg-yellow-50 dark:bg-yellow-900"
                inputmode="numeric"
            ></flux:input>
            <flux:input.group.suffix>Wdh.</flux:input.group.suffix>
        </flux:input.group>
    </flux:table.cell>
    <flux:table.cell>
        <flux:input.group>
            <flux:input
                wire:model.lazy="order"
                wire:dirty.class="bg-yellow-50 dark:bg-yellow-900"
                inputmode="numeric"
            ></flux:input>
        </flux:input.group>
    </flux:table.cell>
    <flux:table.cell>
        <flux:button wire:click="$parent.removeExercise({{ $exerciseId }})" variant="danger" icon="trash"/>
    </flux:table.cell>
</flux:table.row>
