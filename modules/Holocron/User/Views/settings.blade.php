<div>
    <flux:field class="max-w-xs">
        <flux:label>Gewicht</flux:label>

        <flux:input.group>
            <flux:input wire:model.blur="weight" />
            <flux:input.group.suffix>kg</flux:input.group.suffix>
        </flux:input.group>

        <flux:error name="weight" />
    </flux:field>
</div>
