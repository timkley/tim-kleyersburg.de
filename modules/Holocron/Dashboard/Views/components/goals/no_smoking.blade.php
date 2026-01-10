@use(Modules\Holocron\User\Enums\GoalType)
@props(['goal', 'streaks'])

<x-holocron-dashboard::goals.base :$goal :$streaks>
    <x-slot:title>Nicht rauchen</x-slot>
    <x-slot:amounts></x-slot:amounts>

    @if ($goal->reached)
        <flux:button
            class="w-full"
            @click="$wire.trackGoal('{{ GoalType::NoSmoking }}', -1);"
        >
            Geraucht
        </flux:button>
    @endif
</x-holocron-dashboard::goals.base>
