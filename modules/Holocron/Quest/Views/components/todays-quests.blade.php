<div>
    @if($todaysQuests->isNotEmpty())
        <flux:card class="space-y-4">
            <flux:heading class="flex items-center justify-between" size="lg">
                Heute

                <flux:button
                    variant="filled"
                    href="{{ route('holocron.quests.daily') }}"
                    icon="calendar"
                    size="sm"
                >
                    Daily Quest
                </flux:button>
            </flux:heading>

            <div class="space-y-2">
                @foreach($todaysQuests as $quest)
                    <livewire:holocron.quest.components.item
                        :$quest
                        :key="'accepted-quest.' . $quest->id"
                    />
                @endforeach
            </div>
        </flux:card>
    @endif
</div>
