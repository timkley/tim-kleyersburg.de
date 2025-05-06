<div>
    @include('holocron.grind.navigation')

    <div class="space-y-4 mt-3">
        <div class="space-y-1">
            @foreach($plans as $plan)
                <flux:text class="flex items-center justify-between">
                    <flux:link href="{{ route('holocron.grind.plans.show', [$plan->id]) }}" wire:navigate>
                        {{ $plan->name }}
                    </flux:link>

                    <flux:button wire:click="delete({{ $plan->id }})" size="sm" icon="trash" variant="subtle"/>
                </flux:text>
            @endforeach
        </div>

        <flux:separator/>

        <flux:heading size="lg">Neuer Plan</flux:heading>

        <form wire:submit="submit" class="space-y-2">
            <flux:input label="Name" wire:model="name"/>

            <flux:button type="submit">Submit</flux:button>
        </form>
    </div>
</div>
