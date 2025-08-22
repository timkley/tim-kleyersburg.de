<flux:text class="flex gap-x-2 items-center" x-data="{ editing: false}">
    <flux:link
        class="break-all flex-1"
        href="{{ $url }}"
        target="_blank"
        x-show="!editing"
    >
        {{ $title }}
    </flux:link>
    <flux:field
        class="flex-1"
        x-show="editing"
    >
        <flux:input
            x-ref="editTitle"
            wire:model.live.debounce="title"
            x-on:keyup.enter="editing = false"
            x-on:keyup.escape="editing = false"
        />
    </flux:field>
    <flux:button
        variant="filled"
        icon="pencil"
        x-on:click="editing = !editing; $nextTick(() => $refs.editTitle.focus())"
    />
</flux:text>
