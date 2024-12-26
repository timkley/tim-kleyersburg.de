<x-slot:title>Wasser</x-slot>

<div>
    @if ($goal)
        <x-heading tag="h2">Wassereinnahme</x-heading>
        <p>Bereits {{ str_replace('.', ',', round($waterIntake / 1000, 1)) }}&nbsp;l getrunken</p>
        <p>Es fehlen noch {{ str_replace('.', ',', round($remaining / 1000, 1)) }}&nbsp;l</p>
        <p>{{ round($percentage) }}%</p>

        <form
            wire:submit="addWaterIntake"
            class="mt-8 max-w-xs"
        >
            <flux:field>
                <flux:label>Trinkmenge</flux:label>
                <flux:input.group>
                    <flux:input
                        wire:model="intake"
                        icon="beaker"
                        placeholder="Trinkmenge eingeben"
                        inputmode="numeric"
                    />
                    <flux:input.group.suffix>ml</flux:input.group.suffix>
                </flux:input.group>
            </flux:field>
        </form>
    @else
        <form
            wire:submit="setWeight"
            class="max-w-xs"
        >
            <flux:field>
                <flux:label>Gewicht</flux:label>
                <flux:input.group>
                    <flux:input
                        wire:model="weight"
                        icon="scale"
                        placeholder="Gewicht eingeben"
                        type="number"
                    />
                    <flux:input.group.suffix>kg</flux:input.group.suffix>
                </flux:input.group>
            </flux:field>
        </form>
    @endif
</div>
