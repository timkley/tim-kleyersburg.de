@props(['withBreadcrumb' => false])
@use(App\Enums\Holocron\QuestStatus)

<div class="flex">
    <flux:dropdown class="h-[25px] flex items-center">
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

    <div class="flex flex-col sm:flex-row sm:items-center ml-1 space-x-3">
        <a
            href="{{ route('holocron.quests', $quest->id) }}"
            wire:navigate
        >
            <span class="truncate">
                {{ $quest->name }}
            </span>
            @if($quest->children()->count())
                <flux:badge size="sm">{{ $quest->children()->count() }}<span class="hidden sm:inline">&nbsp;Unter-Quests</span></flux:badge>
            @endif
        </a>
        @php($crumbs = $quest->getBreadcrumb()->reverse()->slice(1))

        @if($withBreadcrumb && $crumbs->count())
            <flux:breadcrumbs class="mt-1">
                @foreach($crumbs as $crumb)
                    <flux:breadcrumbs.item class="!font-light" href="{{ route('holocron.quests', $crumb->id) }}" wire:navigate>
                        {{ $crumb->name }}
                    </flux:breadcrumbs.item>
                @endforeach
            </flux:breadcrumbs>
        @endif
    </div>

    <div class="flex items-center h-[25px] ml-auto">
        <flux:button icon="trash" wire:click="$parent.deleteQuest({{ $quest->id }})" wire:confirm="Wirklich löschen?" variant="subtle"></flux:button>
    </div>
</div>
