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
    </flux:modal>
</div>
