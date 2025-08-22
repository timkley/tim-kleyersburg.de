<flux:navbar>
    <flux:navbar.item href="{{ route('holocron.gear') }}" wire:navigate>Übersicht</flux:navbar.item>
    <flux:navbar.item href="{{ route('holocron.gear.items') }}" wire:navigate>Gegenstände</flux:navbar.item>
    <flux:navbar.item href="{{ route('holocron.gear.categories') }}" wire:navigate>Kategorien</flux:navbar.item>
</flux:navbar>
