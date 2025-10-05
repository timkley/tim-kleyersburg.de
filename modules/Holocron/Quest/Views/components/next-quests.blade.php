<div>
    <flux:card class="space-y-4">
        <flux:heading class="flex items-center justify-between" size="lg">
            Ungeplant

            <flux:button
                variant="filled"
                inset
                href="{{ route('holocron.quests.recurring') }}"
                icon="history"
                size="sm"
            >
                Recurring
            </flux:button>
        </flux:heading>

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
