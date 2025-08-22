<div @class([
    'bg-gray-100 dark:bg-gray-800 rounded-md p-4 space-y-2 relative',
    'ml-8' => $note->role === 'assistant'
])>
    <div class="prose group"
         @if(empty($note->content) && $note->role === 'assistant') wire:stream="streamedAnswer" data-loading @endif
    >
        {!! $note->content !!}
        <flux:icon class="hidden group-data-loading:block" icon="loading" />
    </div>
    <div class="text-sm flex items-center justify-between mt-2">
        {{ $note->created_at->format('d.m.Y H:i') }}

        <flux:button wire:click="$parent.deleteNote({{ $note->id }})" variant="subtle" icon="trash" size="sm"/>
    </div>
</div>
