<flux:card class="h-full hover:bg-white/75 dark:hover:bg-white/5">
    <div>
        <flux:heading
            class="flex items-center gap-2 font-semibold"
            size="lg"
        >
            <flux:icon.pill />
            Kreatin
        </flux:heading>
        <flux:subheading> Heute {{ $count }} {{ $count === 1 ? 'Portion' : 'Portionen' }} eingenommen</flux:subheading>
        <flux:button
            class="mt-3"
            wire:click="addPortion"
            >Portion hinzufügen</flux:button
        >
    </div>
</flux:card>
