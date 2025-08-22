@use(Modules\Holocron\User\Enums\GoalType)

<x-holocron-dashboard::goals.base :$goal>
    <x-slot:title>Kreatin</x-slot>

    @unless ($goal->reached)
        <flux:button
            class="w-full"
            @click="$wire.trackGoal('{{ GoalType::Creatine }}', 5);"
        >
            Kreatin genommen
        </flux:button>
    @endunless
</x-holocron-dashboard::goals.base>
