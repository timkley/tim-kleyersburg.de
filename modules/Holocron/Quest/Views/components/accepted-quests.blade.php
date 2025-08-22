<div>
    @if($acceptedQuests->isNotEmpty())
        <flux:card class="space-y-4">
            <flux:heading>Angenommene Quests</flux:heading>

            <div class="space-y-2">
                @foreach($acceptedQuests as $quest)
                    <livewire:holocron.quest.components.item
                        :$quest
                        :key="'accepted-quest.' . $quest->id"
                    />
                @endforeach
            </div>
        </flux:card>
    @endif
</div>
