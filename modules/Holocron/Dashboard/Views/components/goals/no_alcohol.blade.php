@use(Modules\Holocron\User\Enums\GoalType)

<x-holocron-dashboard::goals.base :$goal>
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
