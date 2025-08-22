<flux:modal name="parent-search" class="space-y-4 w-[calc(100vw-var(--spacing)*10)]">
    <flux:heading size="lg">Über-Quest auswählen</flux:heading>

    <flux:input placeholder="Suche..." wire:model.live.debounce="searchTerm"></flux:input>

    <div class="space-y-2">
        @foreach($quests as $quest)
            <flux:button
                class="w-full [&>span]:truncate"
                wire:key="{{ $quest->id }}"
                x-on:click="$dispatch('select', {{ $quest->id }})"
            >
                {{ $quest->name }} auswählen
            </flux:button>
        @endforeach
    </div>
</flux:modal>
