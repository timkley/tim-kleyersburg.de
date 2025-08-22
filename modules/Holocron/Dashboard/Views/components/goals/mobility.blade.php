@use(Modules\Holocron\User\Enums\GoalType)

<x-holocron-dashboard::goals.base :$goal>
    <x-slot:title>Mobility</x-slot>
    <x-slot:amounts></x-slot:amounts>

    @if (!$goal->reached)
        <flux:button
            class="w-full"
            @click="$wire.trackGoal('{{ GoalType::Mobility }}', 1);"
        >
            Gedehnt
        </flux:button>
    @endif
</x-holocron-dashboard::goals.base>
