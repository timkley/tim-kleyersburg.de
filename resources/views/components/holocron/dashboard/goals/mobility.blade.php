@use(App\Enums\Holocron\Health\GoalTypes)

<x-holocron.dashboard.goals.base :$goal>
    <x-slot:title>Mobility</x-slot>
    <x-slot:amounts></x-slot:amounts>

    @if (!$goal->reached)
        <flux:button
                class="w-full"
                @click="$wire.trackGoal('{{ GoalTypes::Mobility }}', 1);"
        >
            Geübt
        </flux:button>
    @endif
</x-holocron.dashboard.goals.base>
