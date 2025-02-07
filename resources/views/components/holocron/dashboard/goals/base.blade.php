@use(App\Models\Holocron\Health\DailyGoal)
@props(['goal'])

<div>
    <flux:heading class="mb-3 flex items-center gap-x-2">
        <span>
            {{ $title }}
        </span>

        @isset($amounts)
            {{ $amounts }}
        @else
            <span> {{ $goal->amount }} / {{ $goal->goal }} {{ $goal->type->unit() }} </span>
        @endisset

        @if ($goal->reached)
            <flux:badge
                size="sm"
                color="sky"
                inset
                >Ziel erreicht 🎉</flux:badge
            >
        @endif
    </flux:heading>

    <flux:subheading>
        <div class="flex items-center gap-x-3">
            <flux:icon.medal class="size-5" />
            <div>
                <p>
                    Streak ×&nbsp;{{ DailyGoal::currentStreakFor($goal->type) }}
                </p>
                <p>
                    Beste ×&nbsp;{{ DailyGoal::highestStreakFor($goal->type) }}
                </p>
            </div>
        </div>
    </flux:subheading>

    <div class="mt-2">
        {{ $slot }}
    </div>
</div>
