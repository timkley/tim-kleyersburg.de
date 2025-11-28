<div class="space-y-8">
    @include('holocron-grind::navigation')

    <flux:input label="Name" wire:model.live.debounce.500ms="form.name" />

    <flux:textarea label="Beschreibung" wire:model.live.debounce.500ms="form.description"></flux:textarea>

    <flux:textarea label="Instruktionen" wire:model.live.debounce.500ms="form.instructions"></flux:textarea>

    @if($exercise->personalRecord())
        <flux:text class="text-base">
            Stärkster Satz: <span class="font-semibold">{{ $exercise->personalRecord()->volume }}&nbsp;kg</span> Volumen
        </flux:text>
    @endif

    <livewire:holocron.grind.components.volume-per-workout-chart :exercise-id="$exercise->id" :key="'chart-' . $exercise->id" />
</div>
