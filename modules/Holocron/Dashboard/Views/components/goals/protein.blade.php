@use(Modules\Holocron\User\Enums\GoalType)
@props(['goal', 'streaks'])

<x-holocron-dashboard::goals.base :$goal :$streaks>
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
