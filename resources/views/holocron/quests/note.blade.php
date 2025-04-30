<div class="bg-gray-100 dark:bg-gray-800 rounded-md p-4 pr-16 space-y-2 relative">
    <p>
        {{ $note->content }}
    </p>
    <p class="text-sm">
        {{ $note->created_at->format('d.m.Y H:i') }}
    </p>

    <div class="absolute top-3 right-3">
        <flux:button wire:click="$parent.deleteNote({{ $note->id }})" variant="subtle" icon="trash" />
    </div>
</div>
