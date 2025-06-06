@use(App\Enums\Holocron\Health\GoalType)

<x-holocron.dashboard.goals.base :$goal>
    <x-slot:title>Planks</x-slot>

    @unless ($goal->reached)
        <form
                @submit.prevent="$wire.trackGoal('{{ GoalType::Planks }}', amount); amount = null"
                x-data="{ amount: null }"
        >
            <flux:input.group>
                <flux:input
                        placeholder="Sekunden"
                        type="number"
                        inputmode="numeric"
                        x-model="amount"
                />

                <flux:button type="submit">geplankt</flux:button>
            </flux:input.group>
        </form>
    @endunless
</x-holocron.dashboard.goals.base>
