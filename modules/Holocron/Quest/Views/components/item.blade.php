<div class="flex">
    <div class="h-[25px] flex items-center">
        @if($quest->is_note)
            <flux:button class="mr-0" inset icon="document-text" variant="ghost"></flux:button>
        @else
            <flux:button
                class="mr-0"
                inset
                icon="{{ $quest->isCompleted() ? 'square-check-big' : 'square' }}"
                variant="ghost"
                wire:click="toggleComplete"
            />
        @endif
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center ml-1 space-x-3">
        <a
            href="{{ route('holocron.quests.show', $quest->id) }}"
            wire:navigate
            @class([
                'line-through' => $quest->isCompleted(),
            ])
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
        @if(! $quest->is_note && ! $quest->isCompleted())
            @if($quest->date)
                <flux:button icon="shield-minus" wire:click="toggleAccept" variant="subtle" size="sm" />
            @else
                <flux:button icon="shield-plus" wire:click="toggleAccept" variant="subtle" size="sm" />
            @endif
        @endif

        <flux:button
            @class([
                'relative after:absolute after:size-2 after:box-content after:rounded-full after:bg-sky-500 after:border-2 after:border-white dark:after:border-zinc-600 after:bottom-1 after:right-1' => $quest->should_be_printed
            ])
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
