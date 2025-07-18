<flux:card class="space-y-4">
    <flux:heading>Nächste Quests</flux:heading>

    <div class="space-y-2">
        @foreach($nextQuests as $quest)
            <livewire:holocron.quests.components.item
                :$quest
                :key="'next-quest.' . $quest->id"
            />
        @endforeach
    </div>
</flux:card>
