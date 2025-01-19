@use(App\Enums\Holocron\Health\IntakeTypes)

<x-holocron.dashboard.goals.base :$goal>
    <x-slot:title>Kreatin</x-slot>

    @unless ($goal->reached)
        <flux:button @click="$wire.trackGoal('{{ IntakeTypes::Creatine }}', 5); amount = null">Kreatin genommen</flux:button>
    @endunless
</x-holocron.dashboard.goals.base>
