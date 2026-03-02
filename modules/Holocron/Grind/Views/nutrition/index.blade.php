<div class="space-y-8">
    @include('holocron-grind::navigation')

    {{-- Date Navigation --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">Ernährung</flux:heading>
        <div class="flex items-center gap-2">
            <flux:button variant="subtle" size="sm" icon="chevron-left" wire:click="previousDay" />
            <flux:input type="date" wire:model.live="date" wire:change="goToDate($event.target.value)" class="w-auto" />
            <flux:button variant="subtle" size="sm" icon="chevron-right" wire:click="nextDay" />
        </div>
    </div>

    {{-- Day Type & Training Label --}}
    <div class="flex flex-wrap items-center gap-4">
        <flux:radio.group wire:model.live="dayType" variant="segmented">
            <flux:radio value="training" label="Training" />
            <flux:radio value="rest" label="Ruhe" />
            <flux:radio value="sick" label="Krank" />
        </flux:radio.group>

        @if($dayType === 'training')
            <flux:input
                size="sm"
                placeholder="Training Label (z.B. Upper, Lower)"
                value="{{ $day?->training_label ?? '' }}"
                wire:change="setTrainingLabel($event.target.value)"
            />
        @endif
    </div>

    {{-- 7-Day Rolling Averages --}}
    <div class="space-y-2">
        <flux:heading size="base">7-Tage Durchschnitt</flux:heading>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <flux:card size="sm" class="space-y-1">
                <flux:text size="sm">Kalorien</flux:text>
                <flux:heading>{{ $averageKcal }} kcal</flux:heading>
                @if($targets)
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Ziel: {{ $targets['kcal'] ?? '-' }}</flux:text>
                @endif
            </flux:card>
            <flux:card size="sm" class="space-y-1">
                <flux:text size="sm">Protein</flux:text>
                <flux:heading>{{ $averageProtein }} g</flux:heading>
                @if($targets)
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Ziel: {{ $targets['protein'] ?? '-' }}</flux:text>
                @endif
            </flux:card>
            <flux:card size="sm" class="space-y-1">
                <flux:text size="sm">Fett</flux:text>
                <flux:heading>{{ $averageFat }} g</flux:heading>
                @if($targets)
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Ziel: {{ $targets['fat'] ?? '-' }}</flux:text>
                @endif
            </flux:card>
            <flux:card size="sm" class="space-y-1">
                <flux:text size="sm">Kohlenhydrate</flux:text>
                <flux:heading>{{ $averageCarbs }} g</flux:heading>
                @if($targets)
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Ziel: {{ $targets['carbs'] ?? '-' }}</flux:text>
                @endif
            </flux:card>
        </div>
    </div>

    {{-- Today's Totals --}}
    @if($day && $day->meals->isNotEmpty())
        <div class="space-y-2">
            <flux:heading size="base">Tagesübersicht</flux:heading>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <flux:card size="sm" class="space-y-1">
                    <flux:text size="sm">Kalorien</flux:text>
                    <flux:heading>{{ $day->meals->sum('kcal') }} kcal</flux:heading>
                </flux:card>
                <flux:card size="sm" class="space-y-1">
                    <flux:text size="sm">Protein</flux:text>
                    <flux:heading>{{ $day->meals->sum('protein') }} g</flux:heading>
                </flux:card>
                <flux:card size="sm" class="space-y-1">
                    <flux:text size="sm">Fett</flux:text>
                    <flux:heading>{{ $day->meals->sum('fat') }} g</flux:heading>
                </flux:card>
                <flux:card size="sm" class="space-y-1">
                    <flux:text size="sm">Kohlenhydrate</flux:text>
                    <flux:heading>{{ $day->meals->sum('carbs') }} g</flux:heading>
                </flux:card>
            </div>
        </div>
    @endif

    <flux:separator />

    {{-- Meals --}}
    <div class="space-y-4">
        <flux:heading size="base">Mahlzeiten</flux:heading>

        @if($day && $day->meals->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                @foreach($day->meals as $meal)
                    <flux:card wire:key="meal-{{ $meal->id }}" size="sm" class="space-y-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <flux:heading size="sm">{{ $meal->name }}</flux:heading>
                                @if($meal->time)
                                    <flux:text size="sm">{{ $meal->time }} Uhr</flux:text>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:button variant="subtle" size="xs" icon="pencil" wire:click="editMeal({{ $meal->id }})" />
                                <flux:button variant="subtle" size="xs" icon="trash" wire:click="deleteMeal({{ $meal->id }})" wire:confirm="Mahlzeit wirklich löschen?" />
                            </div>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <flux:text>{{ $meal->kcal }} kcal</flux:text>
                            <flux:text>P: {{ $meal->protein }}g</flux:text>
                            <flux:text>F: {{ $meal->fat }}g</flux:text>
                            <flux:text>K: {{ $meal->carbs }}g</flux:text>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        @else
            <flux:text>Noch keine Mahlzeiten eingetragen.</flux:text>
        @endif
    </div>

    <flux:separator />

    {{-- Add Meal Form --}}
    <div class="space-y-4">
        <flux:heading size="base">{{ $editingMealId !== null ? 'Mahlzeit bearbeiten' : 'Mahlzeit hinzufügen' }}</flux:heading>
        <form wire:submit="{{ $editingMealId !== null ? 'updateMeal' : 'addMeal' }}" class="space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                <flux:input label="Name" wire:model="mealName" placeholder="z.B. Frühstück" required />
                <flux:input label="Uhrzeit" type="time" wire:model="mealTime" />
                <flux:input label="Kalorien (kcal)" type="number" wire:model="mealKcal" min="0" required />
                <flux:input label="Protein (g)" type="number" wire:model="mealProtein" min="0" required />
                <flux:input label="Fett (g)" type="number" wire:model="mealFat" min="0" required />
                <flux:input label="Kohlenhydrate (g)" type="number" wire:model="mealCarbs" min="0" required />
            </div>
            <div class="flex items-center gap-2">
                <flux:button type="submit" variant="primary">
                    {{ $editingMealId !== null ? 'Mahlzeit aktualisieren' : 'Mahlzeit speichern' }}
                </flux:button>
                @if($editingMealId !== null)
                    <flux:button type="button" variant="subtle" wire:click="cancelMealEdit">Abbrechen</flux:button>
                @endif
            </div>
        </form>
    </div>

    <flux:separator />

    {{-- Notes --}}
    <div class="space-y-4">
        <flux:heading size="base">Notizen</flux:heading>
        <flux:textarea
            wire:change="updateNotes($event.target.value)"
            placeholder="Notizen zum Tag..."
            rows="3"
        >{{ $day?->notes ?? '' }}</flux:textarea>
    </div>
</div>
