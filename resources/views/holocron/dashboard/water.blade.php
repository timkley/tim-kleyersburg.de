<flux:card class="flex h-full flex-col gap-3 hover:bg-white/75 dark:hover:bg-white/5">
    <div>
        <flux:heading
            class="flex items-center gap-2 font-semibold"
            size="lg"
        >
            <flux:icon.glass-water />
            Wassereinnahme
        </flux:heading>
        <flux:subheading> Heute {{ str_replace('.', ',', round($waterIntake / 1000, 1)) }}&nbsp;l getrunken </flux:subheading>
        @if ($remainingWater > 0)
            <flux:subheading> Es fehlen noch {{ str_replace('.', ',', round($remainingWater / 1000, 1)) }}&nbsp;l </flux:subheading>
        @endif
    </div>

    <flux:button.group class="mt-auto">
        <flux:button
            class="flex-1"
            wire:click="addBottle"
            >Flasche getrunken</flux:button
        >
        <flux:button
            href="{{ route('holocron.water') }}"
            icon="cog"
        ></flux:button>
    </flux:button.group>
</flux:card>
