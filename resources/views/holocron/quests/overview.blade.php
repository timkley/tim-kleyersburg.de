@use(App\Models\Holocron\Quest)
@use(App\Enums\Holocron\QuestStatus)

<div>
    <div class="space-y-4">
        @if($acceptedQuests->isNotEmpty())
            <flux:card class="space-y-4">
                <flux:heading>Angenommene Quests</flux:heading>

                <div class="space-y-2">
                    @foreach($acceptedQuests as $acceptedQuest)
                        <livewire:holocron.quests.item
                            :quest="$acceptedQuest"
                            :key="'accepted-item.' . $acceptedQuest->id"
                        />
                    @endforeach
                </div>
            </flux:card>
        @endif

        <flux:card class="space-y-4">
            <flux:heading>Nächste Quests</flux:heading>

            <div class="space-y-2">
                @foreach($questsWithoutChildren as $leafQuest)
                    <livewire:holocron.quests.item
                        :quest="$leafQuest"
                        :key="'leaf-item.' . $leafQuest->id"
                    />
                @endforeach
            </div>
        </flux:card>

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
                        <flux:text>Wird abgelegt unter <span class="font-medium">{{ $parentQuestName }}</span></flux:text>
                    @endif
                </form>

                <div class="space-y-2">
                    @foreach($quests as $childQuest)
                        <livewire:holocron.quests.item
                            :quest="$childQuest"
                            :key="'item.' . $childQuest->id"
                        />
                    @endforeach
                </div>
            </div>
        </flux:card>
    </div>

    <livewire:holocron.quests.parent-search @select="setParentQuest($event.detail)" />
</div>
