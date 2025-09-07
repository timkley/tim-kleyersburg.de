<flux:modal name="parent-search" class="space-y-4 w-[calc(100vw-var(--spacing)*10)]">
    <flux:heading size="lg">Über-Quest auswählen</flux:heading>

    <flux:select x-on:change="$dispatch('select', $event.target.value)" variant="combobox" :filter="false">
        <x-slot name="input">
            <flux:select.input autofocus wire:model.live.debounce="searchTerm"/>
        </x-slot>

        @foreach($quests as $quest)
            <flux:select.option value="{{ $quest->id }}" wire:key="{{ $quest->id }}">
                {{ $quest->breadcrumb()->pluck('name')->join(' > ') }}
            </flux:select.option>
        @endforeach
    </flux:select>
</flux:modal>
