<div class="space-y-8">
    @include('holocron-grind::navigation')

    <div class="space-y-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            @foreach($exercises as $exercise)
                <a href="{{ route('holocron.grind.exercises.show', $exercise->id) }}" wire:navigate>
                    <flux:card
                        size="sm"
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-700 space-y-2"
                    >
                        <flux:text>
                            {{ $exercise->name }}
                        </flux:text>
                    </flux:card>
                </a>
            @endforeach
            <flux:modal.trigger name="new-exercise">
                <flux:button class="h-full min-h-10" variant="primary">Neue Übung</flux:button>
            </flux:modal.trigger>
        </div>

        <livewire:holocron.grind.exercises.create-modal />
    </div>
</div>
