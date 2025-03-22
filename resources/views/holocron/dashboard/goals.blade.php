<flux:card class="sm:col-span-2 md:col-span-3">
    <flux:heading
        class="flex items-center gap-2 font-semibold"
        size="lg"
    >
        <flux:icon.trophy/>
        Deine Ziele

        <flux:subheading>{{ $goalsPast20DaysReachedCount }} / {{ $goalsPast20DaysCount }} erreichte Ziele</flux:subheading>
        <flux:subheading>Letzte Periode: {{ $goalsPast40DaysReachedCount }} / {{ $goalsPast40DaysCount }} erreichte Ziele</flux:subheading>
    </flux:heading>

    <x-holocron.dashboard.goals.calendar :$goalsPast20DaysByDay :$selectedDate/>

    <div class="mt-4 grid gap-x-16 gap-y-8 sm:grid-cols-2 md:gap-x-20 lg:grid-cols-3">
        @foreach ($todaysGoals as $goal)
            <x-dynamic-component
                :component="'holocron.dashboard.goals.'.$goal->type->value"
                :goal="$goal"
            />
            @if(!$loop->last)
                <flux:separator class="sm:hidden"/>
            @endif
        @endforeach
    </div>
</flux:card>
