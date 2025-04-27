@props(['withBreadcrumb' => false])
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

    <div class="flex items-center ml-1 space-x-3">
        <a
            href="{{ route('holocron.quests', $quest->id) }}"
            wire:navigate
        >
            <span>
                {{ $quest->name }}
            </span>
        </a>
        @if($withBreadcrumb)
            <flux:breadcrumbs>
                @foreach($quest->getBreadcrumb()->reverse()->slice(1) as $crumb)
                    <flux:breadcrumbs.item href="{{ route('holocron.quests', $crumb->id) }}" wire:navigate>
                        {{ $crumb->name }}
                    </flux:breadcrumbs.item>
                @endforeach
            </flux:breadcrumbs>
        @else
            <flux:badge size="sm">{{ $quest->children()->count() }} Unter-Quests</flux:badge>
        @endif
    </div>

    <flux:button class="ml-auto" icon="trash" wire:click="$parent.deleteQuest({{ $quest->id }})" wire:confirm="Wirklich löschen?" variant="subtle"></flux:button>
</div>
