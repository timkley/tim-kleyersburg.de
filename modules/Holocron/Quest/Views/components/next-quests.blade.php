<div>
    <flux:card class="space-y-4">
        <flux:heading size="lg">Ungeplant</flux:heading>

        <form wire:submit="addQuest">
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

        <div class="space-y-2">
            @foreach($nextQuests as $quest)
                <livewire:holocron.quest.components.item
                    :$quest
                    :key="'next-quest.' . $quest->id"
                />
            @endforeach
        </div>
    </flux:card>

    <livewire:holocron.quest.components.parent-search @select="setParentQuest($event.detail)"/>
</div>
