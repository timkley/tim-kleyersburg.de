@use(App\Enums\Holocron\QuestStatus)

<div class="flex items-center">
    <flux:dropdown>
        <flux:button :icon="$quest->status->icon()" variant="ghost"></flux:button>

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

    <a
        href="{{ route('holocron.quests', $quest->id) }}"
        wire:navigate
        class="flex items-center cursor-pointer"
    >
        <span>
            {{ $quest->name }}
        </span>
        <flux:badge class="ml-3" size="sm">{{ $quest->children()->count() }} Unter-Quests</flux:badge>
    </a>

    <flux:button class="ml-auto" icon="trash" wire:click="$parent.deleteQuest({{ $quest->id }})" variant="subtle"></flux:button>
</div>
