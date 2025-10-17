<div>
    <flux:modal name="search-modal"
                variant="bare"
                class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden px-4">
        <flux:command :filter="false">
            <flux:command.input
                placeholder="Quests suchen..."
                autofocus
                wire:model.live.debounce="query"
            />

            @if($results)
                <flux:command.items>
                    @foreach($results as $result)
                        <flux:command.item class="!h-auto" x-on:click="Livewire.navigate('{{ route('holocron.quests.show', $result->id) }}')" wire:key="{{ $result->id }}">
                            {{ $result->name }}
                        </flux:command.item>
                    @endforeach
                </flux:command.items>
            @endif

        </flux:command>

        <div class="mx-auto w-fit mt-2 p-2 bg-white border-b-zinc-200 dark:bg-zinc-700 dark:border-zinc-600 rounded-md backdrop-blur-md shadow-xs">
            <flux:checkbox wire:model="includeCompleted" label="Abgeschlossene Quests einbeziehen" />
        </div>
    </flux:modal>
</div>
