<div>
    <flux:card class="space-y-4">
        <div class="space-y-4">
            <flux:input
                wire:model.live.debounce="query"
                placeholder="Suche"
            />

            @if($searchResults)
                <div class="space-y-2" wire:key="search-results">
                    @foreach($searchResults as $searchResult)
                        <livewire:holocron.quest.components.item
                            :quest="$searchResult"
                            :key="'search-quest.' . $searchResult->id"
                        />
                    @endforeach
                </div>
            @endif

            @if($notes->isNotEmpty())
                <div class="space-y-2" wire:key="main-notes">
                    <flux:heading>Notizen</flux:heading>
                    @foreach($notes as $quest)
                        <livewire:holocron.quest.components.item
                            :$quest
                            :key="'main-note.' . $quest->id"
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </flux:card>
</div>
