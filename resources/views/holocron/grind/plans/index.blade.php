<div>
    @include('holocron.grind.navigation')

    <div class="space-y-4 mt-3">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            @foreach($plans as $plan)
                <a href="{{ route('holocron.grind.plans.show', $plan->id) }}" wire:navigate>
                    <flux:card size="sm">
                        <flux:text class="flex items-center justify-between">
                            {{ $plan->name }}
                        </flux:text>
                    </flux:card>
                </a>
            @endforeach

            <flux:modal.trigger name="new">
                <flux:button class="h-full min-h-10" variant="primary">Neuer Plan</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="new" variant="flyout">
            <form wire:submit="submit" class="space-y-2">
                <flux:input label="Name" wire:model="name"/>

                <flux:button type="submit" variant="primary">Speichern</flux:button>
            </form>
        </flux:modal>
    </div>
</div>
