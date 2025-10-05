<div>
    <flux:card class="space-y-2">
        @foreach($recurringQuests as $recurrence)
            <div class="flex justify-between">
                <a href="{{ route('holocron.quests.show', [$recurrence->quest->id]) }}">
                    <p class="font-bold">
                        {{ $recurrence->quest->name }}
                    </p>
                    <p class="text-sm">
                        {{ $recurrence->quest->breadcrumb()->pluck('name')->join(' > ') }}
                    </p>
                </a>
                <span>
                    alle {{ $recurrence->every_x_days }} Tage
                </span>
            </div>
        @endforeach
    </flux:card>
</div>
