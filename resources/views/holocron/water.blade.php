<x-slot:title>Wasser</x-slot>

<div>
    @if ($goal)
        <x-heading tag="h2">Wassereinnahme</x-heading>

        <form
            wire:submit="addWaterIntake"
            class="mb-8 max-w-xs"
        >
            <flux:field>
                <flux:input.group>
                    <flux:input
                        wire:model="intake"
                        icon="beaker"
                        placeholder="Trinkmenge eingeben"
                        inputmode="numeric"
                    />
                    <flux:input.group.suffix wire:click="addWaterIntake">ml</flux:input.group.suffix>
                </flux:input.group>

                <flux:error name="intake" />
            </flux:field>
        </form>

        <p>Bereits {{ str_replace('.', ',', round($waterIntake / 1000, 1)) }}&nbsp;l getrunken</p>
        @if ($remainingWater > 0)
            <p>Es fehlen noch {{ str_replace('.', ',', round($remainingWater / 1000, 1)) }}&nbsp;l</p>
        @endif
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
