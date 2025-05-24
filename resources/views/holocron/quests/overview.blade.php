@use(App\Models\Holocron\Quest)
@use(App\Enums\Holocron\QuestStatus)

<div>
    <div class="space-y-4">
        <flux:card class="space-y-4">
            <div>
                <flux:heading>Nächste Quests</flux:heading>
                <flux:text class="mt-1">Quests, an denen als nächstes gearbeitet werden sollte, da sie keine
                    Sidequests haben.
                </flux:text>
            </div>

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
            <div>
                <flux:heading>Main-Quests</flux:heading>
            </div>
            <div class="space-y-4">
                <div class="space-y-2">
                    @foreach($quests as $childQuest)
                        <livewire:holocron.quests.item
                            :quest="$childQuest"
                            :key="'item.' . $childQuest->id"
                        />
                    @endforeach
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <form wire:submit="addQuest" class="max-w-lg flex-1">
                        <flux:input wire:model="questDraft" placeholder="Neue Quest"/>
                    </form>
                </div>
            </div>
        </flux:card>
    </div>
</div>
