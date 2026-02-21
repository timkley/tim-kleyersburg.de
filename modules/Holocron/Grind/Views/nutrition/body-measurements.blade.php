<div class="space-y-8">
    @include('holocron-grind::navigation')

    <flux:heading size="lg">Körper</flux:heading>

    {{-- Weight Chart --}}
    @if(count($chartData) >= 2)
        <div class="space-y-2">
            <flux:heading size="base">Gewicht</flux:heading>
            <flux:chart :value="$chartData" class="aspect-[3/1]">
                <flux:chart.svg>
                    <flux:chart.cursor />
                    <flux:chart.line field="weight" curve="none" class="text-sky-600" />

                    <flux:chart.axis axis="x" field="date" tick-count="10">
                        <flux:chart.axis.mark />
                        <flux:chart.axis.tick />
                        <flux:chart.axis.line />
                    </flux:chart.axis>

                    <flux:chart.axis axis="y" field="weight" tick-start="min" :format="['notation' => 'compact', 'useGrouping' => true, 'style' => 'unit', 'unit' => 'kilogram']">
                        <flux:chart.axis.grid />
                        <flux:chart.axis.tick />
                    </flux:chart.axis>
                </flux:chart.svg>
                <flux:chart.tooltip>
                    <flux:chart.tooltip.heading field="date" />
                    <flux:chart.tooltip.value field="weight" label="Gewicht" />
                </flux:chart.tooltip>
            </flux:chart>
        </div>
    @endif

    {{-- Muscle Mass Chart --}}
    @php
        $muscleMassData = array_values(array_filter($chartData, fn ($entry) => $entry['muscle_mass'] !== null));
    @endphp
    @if(count($muscleMassData) >= 2)
        <div class="space-y-2">
            <flux:heading size="base">Muskelmasse</flux:heading>
            <flux:chart :value="$muscleMassData" class="aspect-[3/1]">
                <flux:chart.svg>
                    <flux:chart.cursor />
                    <flux:chart.line field="muscle_mass" curve="none" class="text-emerald-600" />

                    <flux:chart.axis axis="x" field="date" tick-count="10">
                        <flux:chart.axis.mark />
                        <flux:chart.axis.tick />
                        <flux:chart.axis.line />
                    </flux:chart.axis>

                    <flux:chart.axis axis="y" field="muscle_mass" tick-start="min" :format="['notation' => 'compact', 'useGrouping' => true, 'style' => 'unit', 'unit' => 'kilogram']">
                        <flux:chart.axis.grid />
                        <flux:chart.axis.tick />
                    </flux:chart.axis>
                </flux:chart.svg>
                <flux:chart.tooltip>
                    <flux:chart.tooltip.heading field="date" />
                    <flux:chart.tooltip.value field="muscle_mass" label="Muskelmasse" />
                </flux:chart.tooltip>
            </flux:chart>
        </div>
    @endif

    <flux:separator />

    {{-- Add Measurement Form --}}
    <div class="space-y-4">
        <flux:heading size="base">Messung hinzufügen</flux:heading>
        <form wire:submit="addMeasurement" class="space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                <flux:input label="Datum" type="date" wire:model="date" required />
                <flux:input label="Gewicht (kg)" type="number" step="0.01" wire:model="weight" min="0" required />
                <flux:input label="Körperfett (%)" type="number" step="0.1" wire:model="bodyFat" min="0" max="100" />
                <flux:input label="Muskelmasse (kg)" type="number" step="0.1" wire:model="muscleMass" min="0" />
                <flux:input label="Viszeralfett" type="number" wire:model="visceralFat" min="0" />
                <flux:input label="BMI" type="number" step="0.1" wire:model="bmi" min="0" />
                <flux:input label="Körperwasser (%)" type="number" step="0.1" wire:model="bodyWater" min="0" max="100" />
            </div>
            <flux:button type="submit" variant="primary">Messung hinzufügen</flux:button>
        </form>
    </div>

    <flux:separator />

    {{-- Measurements Table --}}
    <div class="space-y-4">
        <flux:heading size="base">Messungen</flux:heading>

        @if($measurements->isEmpty())
            <flux:text>Noch keine Messungen vorhanden.</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Datum</flux:table.column>
                    <flux:table.column>Gewicht</flux:table.column>
                    <flux:table.column>Körperfett</flux:table.column>
                    <flux:table.column>Muskelmasse</flux:table.column>
                    <flux:table.column>BMI</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($measurements as $measurement)
                        <flux:table.row wire:key="measurement-{{ $measurement->id }}">
                            <flux:table.cell>{{ $measurement->date->format('d.m.Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $measurement->weight }} kg</flux:table.cell>
                            <flux:table.cell>{{ $measurement->body_fat !== null ? $measurement->body_fat . ' %' : '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $measurement->muscle_mass !== null ? $measurement->muscle_mass . ' kg' : '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $measurement->bmi !== null ? $measurement->bmi : '-' }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
