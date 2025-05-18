<div class="space-y-8">
    @include('holocron.grind.navigation')

    <flux:heading size="xl">{{ $exercise->name }}</flux:heading>

    <flux:text class="text-base">
        Stärkster Satz: <span class="font-semibold">{{ $exercise->personalRecord()->volume }}&nbsp;kg</span> Volumen
    </flux:text>

    <flux:chart :value="$data" class="aspect-[3/1]">
        <flux:chart.svg>
            <flux:chart.cursor />

            <flux:chart.line field="total_volume" curve="none" class="text-sky-600" />

            <flux:chart.axis axis="x" field="date" tick-count="10">
                <flux:chart.axis.mark />
                <flux:chart.axis.tick />
                <flux:chart.axis.line />
            </flux:chart.axis>

            <flux:chart.axis axis="y" field="total_volume" tick-start="min" :format="['notation' => 'compact', 'useGrouping' => true, 'style' => 'unit', 'unit' => 'kilogram']">
                <flux:chart.axis.grid />
                <flux:chart.axis.tick />
            </flux:chart.axis>
        </flux:chart.svg>

        <flux:chart.tooltip>
            <flux:chart.tooltip.heading field="date" />
            <flux:chart.tooltip.value field="total_volume" label="Volumen" />
        </flux:chart.tooltip>
    </flux:chart>
</div>
