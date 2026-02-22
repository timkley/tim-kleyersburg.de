@props(['goal', 'streaks'])

<x-holocron-dashboard::goals.base :$goal :$streaks>
    <x-slot:title>Protein</x-slot>

    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
        Wird automatisch ueber Ernaehrung synchronisiert.
    </flux:text>
</x-holocron-dashboard::goals.base>
