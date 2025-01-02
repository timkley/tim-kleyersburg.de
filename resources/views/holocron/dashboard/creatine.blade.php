<flux:card class="flex h-full flex-col gap-3 hover:bg-white/75 dark:hover:bg-white/5">
    <div>
        <flux:heading
            class="flex items-center gap-2 font-semibold"
            size="lg"
        >
            <flux:icon.pill />
            Kreatin
        </flux:heading>
        <flux:subheading> Heute {{ $count }} {{ $count === 1 ? 'Portion' : 'Portionen' }} eingenommen</flux:subheading>
    </div>
    <flux:button
        class="mt-auto"
        wire:click="addPortion"
        >Portion hinzufügen
    </flux:button>
</flux:card>
