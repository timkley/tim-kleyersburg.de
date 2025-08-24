
<div>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <flux:button variant="filled" wire:click="previousDay" icon="arrow-left" />
            <flux:button variant="filled" wire:click="goToToday">Heute</flux:button>
            <flux:button variant="filled" wire:click="nextDay" icon="arrow-right" />
            <flux:date-picker wire:model.live="date" />
        </div>
        <div class="text-lg font-semibold">
            {{ $this->currentDate()->format('d. F') }}
        </div>
    </div>

    <div class="mt-4">
        <livewire:holocron.quest.show
            :quest="$quest"
            wire:key="{{ $this->currentDate()->format('Y-m-d') }}"
        />
    </div>
</div>

