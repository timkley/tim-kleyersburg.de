<div>
    @include('holocron.grind.navigation')

    <div class="space-y-4 mt-3">
        <div class="space-y-1">
            @foreach($exercises as $exercise)
                <flux:text class="flex items-center justify-between">
                    {{ $exercise->name }}

                    <flux:button wire:click="delete({{ $exercise->id }})" size="sm" icon="trash" variant="subtle" />
                </flux:text>
            @endforeach
        </div>

        <flux:separator />

        <flux:heading size="lg">Neue Übung</flux:heading>

        <form wire:submit="submit" class="space-y-2">
            <flux:input label="Name" wire:model="name" />
            <flux:input label="Beschreibung" wire:model="description" />
            <flux:input label="Instruktionen" wire:model="instructions" />

            <flux:button type="submit">Submit</flux:button>
        </form>
    </div>
</div>
