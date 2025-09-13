@use(Modules\Holocron\Quest\Enums\QuestStatus)

<div class="flex">
    <flux:dropdown class="h-[25px] flex items-center">
        <flux:button class="mr-0" inset :icon="$quest->status->icon()" variant="ghost"></flux:button>

        <flux:menu wire:replace>
            @foreach(QuestStatus::cases() as $status)
                <flux:menu.item
                    :icon="$status->icon()"
                    :disabled="$status->value === $quest->status->value"
                    wire:click="setStatus('{{ $status->value }}')"
                    wire:key="{{ $status->value }}"
                >
                    {{ $status->label() }}
                </flux:menu.item>
            @endforeach
        </flux:menu>
    </flux:dropdown>

    <div class="flex flex-col sm:flex-row sm:items-center ml-1 space-x-3">
        <a
            href="{{ route('holocron.quests.show', $quest->id) }}"
            wire:navigate
        >
            <span>
                {{ $quest->name }}
            </span>
            @if($quest->children->count())
                <flux:badge size="sm">{{ $quest->children->count() }}
                    <span class="hidden sm:inline">&nbsp;Unter-Quests</span></flux:badge>
            @endif
        </a>
        @if($quest->parent && $showParent)
            <flux:link class="text-sm"
                       href="{{ route('holocron.quests.show', $quest->parent->id) }}">{{ $quest->parent->name }}</flux:link>
        @endif
    </div>

    <div class="flex items-center h-[25px] ml-auto">
        @if($quest->status !== QuestStatus::Note && $quest->status !== QuestStatus::Complete)
            @if($quest->date)
                <flux:button icon="shield-minus" wire:click="toggleAccept" variant="subtle" size="sm" />
            @else
                <flux:button icon="shield-plus" wire:click="toggleAccept" variant="subtle" size="sm" />
            @endif
        @endif

        <flux:button
            icon="printer"
            wire:click="print"
            variant="subtle"
            size="sm"
        />

        <flux:button
            icon="trash"
            wire:click="deleteQuest({{ $quest->id }})"
            wire:confirm="Willst du {{ $quest->name }} wirklich löschen?"
            variant="subtle"
            size="sm"
        />
    </div>
</div>
