@use(App\Enums\Holocron\Health\GoalTypes)

<x-holocron.dashboard.goals.base :$goal>
    <x-slot:title>Wasser</x-slot>

    <form
            @submit.prevent="$wire.trackGoal('{{ GoalTypes::Water }}', amount); amount = null"
            x-data="{ amount: null }"
    >
        <flux:input.group>
            <flux:input
                    placeholder="Wasser in ml"
                    type="number"
                    inputmode="numeric"
                    x-model="amount"
            />

            <flux:button type="submit">getrunken</flux:button>
        </flux:input.group>
    </form>
</x-holocron.dashboard.goals.base>
