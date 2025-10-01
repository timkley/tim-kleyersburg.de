<flux:navbar>
    <flux:navbar.item href="{{ route('holocron.grind.workouts.index') }}" wire:navigate>Workouts</flux:navbar.item>
    <flux:navbar.item href="{{ route('holocron.grind.exercises.index') }}" wire:navigate>Übungen</flux:navbar.item>
    <flux:navbar.item href="{{ route('holocron.grind.plans.index') }}" wire:navigate>Pläne</flux:navbar.item>
    <flux:navbar.item href="{{ route('holocron.grind.health-data') }}" wire:navigate>Health Data</flux:navbar.item>
</flux:navbar>
