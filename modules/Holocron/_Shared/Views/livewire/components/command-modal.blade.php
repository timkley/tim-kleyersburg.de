<div>
    <flux:modal name="command-modal"
                variant="bare"
                class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden px-4">
        <flux:command.no-clear :filter="false">
            <flux:command.input
                icon="book-check"
                placeholder="Neue Quest erstellen..."
                autofocus
                wire:model="name"
                x-bind:class="{ '!bg-sky-100 dark:!bg-sky-900': $wire.hasIntent }"
                x-on:keyup.slash="$wire.hasIntent = true"
                x-on:input="
                    if ($wire.hasIntent) $wire.loadIntents()
                "
                x-on:keydown.enter.prevent="
                    if ($wire.hasIntent) {
                        return
                    }

                    if ($event.shiftKey) {
                        $wire.createQuest(true)
                    } else {
                        $wire.createQuest(false)
                    }
                "
                x-on:keydown.escape="
                    if ($wire.hasIntent) {
                        $wire.hasIntent = false
                        $event.preventDefault()
                        return
                    }
                "
            />
            @foreach($labels as $type => $label)
                <flux:text class="px-4 py-2 bg-white dark:bg-zinc-700 flex items-center gap-x-2">
                    @php
                        $icon = match($type) {
                            'parent-quest' => 'book-check',
                            default => 'arrow-turn-down-right'
                        }
                    @endphp

                    <flux:icon :name="$icon" class="size-4" />>

                    {{ $label }}
                </flux:text>
            @endforeach

            @if($results)
                <flux:command.items>
                    @foreach($results as $result)
                        <flux:command.item class="!h-auto" wire:click="processIntent({{ json_encode($result) }})" wire:key="{{ $result->type . '_' . $result->value }}">
                            {{ $result->label }}
                        </flux:command.item>
                    @endforeach
                </flux:command.items>
            @endif
        </flux:command.no-clear>
    </flux:modal>
</div>
