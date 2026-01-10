@use(Modules\Holocron\User\Enums\GoalType)
@props(['goal', 'streaks'])

<x-holocron-dashboard::goals.base :$goal :$streaks>
    <x-slot:title>Kein Alkohol</x-slot>
    <x-slot:amounts></x-slot:amounts>

    @if ($goal->reached)
        <flux:button
            class="w-full"
            @click="$wire.trackGoal('{{ GoalType::NoAlcohol }}', -1);"
        >
            Getrunken
        </flux:button>
    @endif
</x-holocron-dashboard::goals.base>
