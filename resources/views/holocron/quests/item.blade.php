@use(App\Enums\Holocron\QuestStatus)

<div class="flex">
    <flux:dropdown class="h-[25px] flex items-center">
        <flux:button class="mr-0" inset :icon="$quest->status->icon()" variant="ghost"></flux:button>

        <flux:menu wire:replace>
            @foreach(QuestStatus::cases() as $status)
                <flux:menu.item
                    :icon="$status->icon()"
                    :disabled="$status->value === $quest->status->value"
                    wire:click="setStatus('{{ $status->value }}')"
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
                <flux:badge size="sm">{{ $quest->children->count() }}<span class="hidden sm:inline">&nbsp;Unter-Quests</span></flux:badge>
            @endif
        </a>
        @if($quest->parent)
            <flux:link class="text-sm" href="{{ route('holocron.quests.show', $quest->parent->id) }}">{{ $quest->parent->name }}</flux:link>
        @endif
    </div>

    <div class="flex items-center h-[25px] ml-auto">
        <flux:button icon="trash" wire:click="$parent.deleteQuest({{ $quest->id }})" wire:confirm="Willst du {{ $quest->name }} wirklich löschen?" variant="subtle"></flux:button>
    </div>
</div>
