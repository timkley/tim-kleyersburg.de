<flux:table.row>
    <flux:table.cell> {{ $exercise->name }} </flux:table.cell>
    <flux:table.cell>
        <flux:input.group>
            <flux:input class="min-w-12" wire:model.live="sets"></flux:input>
            <flux:input.group.suffix>Sets</flux:input.group.suffix>
        </flux:input.group>
    </flux:table.cell>
    <flux:table.cell>
        <flux:input.group>
            <flux:input class="min-w-12" wire:model.live="minReps"></flux:input>
            <flux:input.group.suffix>Wdh.</flux:input.group.suffix>
        </flux:input.group>
    </flux:table.cell>
    <flux:table.cell>
        <flux:input.group>
            <flux:input class="min-w-12" wire:model.live="maxReps"></flux:input>
            <flux:input.group.suffix>Wdh.</flux:input.group.suffix>
        </flux:input.group>
    </flux:table.cell>
    <flux:table.cell>
        <flux:input.group>
            <flux:input wire:model.live="order"></flux:input>
        </flux:input.group>
    </flux:table.cell>
</flux:table.row>
