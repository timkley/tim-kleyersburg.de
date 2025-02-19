<flux:table.row>
    <flux:table.cell>
        <flux:input
            wire:model.live="english"
            value="{{ $word->english }}"
        />
    </flux:table.cell>
    <flux:table.cell>
        <flux:input
            wire:model.live="german"
            value="{{ $word->german }}"
        />
    </flux:table.cell>
    <flux:table.cell>
        {{ $word->score() }} (<span class="text-green-500/80">{{ $word->right }}</span> /
        <span class="text-red-500/80">{{ $word->wrong }}</span>)
    </flux:table.cell>
    <flux:table.cell>{{ $word->created_at->format('d.m.Y H:i') }}</flux:table.cell>
    <flux:table.cell>
        <flux:button
            wire:click="deleteWord({{ $word->id }})"
            icon="trash"
            variant="danger"
            size="xs"
        />
    </flux:table.cell>
</flux:table.row>
