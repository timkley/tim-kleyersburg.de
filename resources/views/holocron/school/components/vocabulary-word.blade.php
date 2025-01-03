<flux:row>
    <flux:cell>
        <flux:input
            wire:model.live="english"
            value="{{ $word->english }}"
        />
    </flux:cell>
    <flux:cell>
        <flux:input
            wire:model.live="german"
            value="{{ $word->german }}"
        />
    </flux:cell>
    <flux:cell>
        {{ $word->score() }} (<span class="text-green-500/80">{{ $word->right }}</span> /
        <span class="text-red-500/80">{{ $word->wrong }}</span>)
    </flux:cell>
    <flux:cell>{{ $word->created_at->format('d.m.Y H:i') }}</flux:cell>
    <flux:cell>
        <flux:button
            wire:click="deleteWord({{ $word->id }})"
            icon="trash"
            variant="danger"
            size="xs"
        />
    </flux:cell>
</flux:row>
