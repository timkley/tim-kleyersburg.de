<div class="space-y-8">
    @include('holocron-grind::navigation')

    <flux:heading size="xl">{{ $exercise->name }}</flux:heading>

    <flux:textarea label="Beschreibung" wire:model.live.debounce="description"></flux:textarea>

    @if($exercise->personalRecord())
        <flux:text class="text-base">
            Stärkster Satz: <span class="font-semibold">{{ $exercise->personalRecord()->volume }}&nbsp;kg</span> Volumen
        </flux:text>
    @endif

    <livewire:holocron.grind.components.volume-per-workout-chart :exercise-id="$exercise->id" :key="'chart-' . $exercise->id" />
</div>
