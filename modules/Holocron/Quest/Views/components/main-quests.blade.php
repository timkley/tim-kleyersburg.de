<div>
    <flux:card class="space-y-4">
        <flux:heading>Main-Quests</flux:heading>

        <div class="space-y-4">
            <form wire:submit="addQuest" class="max-w-lg">
                <div class="flex gap-x-2">
                    <flux:input wire:model="questDraft" placeholder="Neue Quest"/>
                    <flux:modal.trigger name="parent-search">
                        <flux:button class="px-4" icon="folder-arrow-down"></flux:button>
                    </flux:modal.trigger>
                </div>
                @if($parentQuestName)
                    <flux:text class="mt-1">Wird abgelegt unter <span class="font-medium">{{ $parentQuestName }}</span></flux:text>
                @endif
            </form>

            <flux:input
                class="mt-4"
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

            <div class="space-y-2" wire:key="main-quests">
                @foreach($tasks as $quest)
                    <livewire:holocron.quest.components.item
                        :$quest
                        :key="'main-quest.' . $quest->id"
                    />
                @endforeach
            </div>

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

    <livewire:holocron.quest.components.parent-search @select="setParentQuest($event.detail)"/>
</div>
