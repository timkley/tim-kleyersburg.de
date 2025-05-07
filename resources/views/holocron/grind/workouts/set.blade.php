<div
    class="grid grid-cols-subgrid col-span-4"
    x-data="{
        started_at: '{{ $set->started_at?->toISOString() }}',
        minutes: '00',
        seconds: '00',

        updateCounter() {
            const diff = Math.floor((new Date() - new Date(this.started_at)) / 1000);
            this.minutes = String(Math.floor(diff / 60)).padStart(2, '0');
            this.seconds = String(diff % 60).padStart(2, '0');
        },

        init() {
            if (! this.started_at) return;
            this.updateCounter();
            setInterval(() => this.updateCounter(), 1000);
        }
    }"
>
    <div class="py-2 font-mono">
        <span class="text-sm">#</span>{{ $iteration }}
    </div>
    <flux:input.group>
        @if($set->finished_at && $set->reps < $minReps)
            <flux:input.group.prefix class="!px-2">
                <flux:icon class="text-red-500" variant="micro" icon="arrow-down" />
            </flux:input.group.prefix>
        @endif
        <flux:input class:input="text-center font-bold" wire:model.lazy="weight" :loading="false" wire:dirty.class="bg-yellow-50 dark:bg-yellow-900" />
            @if($set->finished_at && $set->reps >= $maxReps)
                <flux:input.group.suffix class="!px-2">
                    <flux:icon class="text-green-700 dark:text-green-600" variant="micro" icon="arrow-up" />
                </flux:input.group.suffix>
            @endif
    </flux:input.group>
    <flux:input.group>
        <flux:input.group.prefix x-on:click="$wire.reps--; $wire.commit()">-</flux:input.group.prefix>
        <flux:input class:input="text-center font-bold" wire:model.lazy="reps" :loading="false"  wire:dirty.class="bg-yellow-50 dark:bg-yellow-900"/>
        <flux:input.group.suffix x-on:click="$wire.reps++; $wire.commit()">+</flux:input.group.suffix>
    </flux:input.group>
    @if(!$set->started_at && !$set->finished_at)
        <flux:button icon="play-circle" wire:click="start"></flux:button>
    @endif
    @if($set->started_at && !$set->finished_at)
        <flux:button icon="stop-circle" class="!text-red-700 dark:!text-red-600" wire:click="finish"></flux:button>
    @endif
    @if($set->started_at && $set->finished_at)
        <flux:button icon="check-circle" variant="subtle" class="!text-green-800 dark:!text-green-700"></flux:button>
    @endif
</div>
