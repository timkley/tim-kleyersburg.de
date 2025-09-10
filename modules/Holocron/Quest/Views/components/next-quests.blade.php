<div>
    <flux:card class="space-y-4">
        <flux:heading size="lg">Ungeplant</flux:heading>

        <div class="space-y-2">
            @foreach($nextQuests as $quest)
                <livewire:holocron.quest.components.item
                    :$quest
                    :key="'next-quest.' . $quest->id"
                />
            @endforeach
        </div>
    </flux:card>
</div>
