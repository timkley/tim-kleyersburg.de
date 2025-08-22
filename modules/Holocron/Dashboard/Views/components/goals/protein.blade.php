@use(Modules\Holocron\User\Enums\GoalType)

<x-holocron-dashboard::goals.base :$goal>
    <x-slot:title>Protein</x-slot>

    <form
        @submit.prevent="$wire.trackGoal('{{ GoalType::Protein }}', amount); amount = null;"
        x-data="{ amount: null }"
    >
        <flux:input.group>
            <flux:input
                placeholder="ml"
                type="number"
                inputmode="numeric"
                x-model="amount"
            />

            <flux:button type="submit">eingenommen</flux:button>
        </flux:input.group>
    </form>
</x-holocron-dashboard::goals.base>
