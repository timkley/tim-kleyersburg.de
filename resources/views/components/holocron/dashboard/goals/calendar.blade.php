@use(Illuminate\Support\Carbon)

<div class="grid grid-cols-5 sm:grid-cols-10 gap-3 mt-6 mb-8">
    @foreach($goalsPast20DaysByDay as $day => $goals)
        @php
            $day = Carbon::parse($day);
            $trophy = $goals->count() === $goals->sum('reached')
        @endphp
        <flux:card
            @class([
                 'relative cursor-pointer flex flex-col gap-2 items-center !p-3',
                 '!bg-sky-100 dark:!bg-sky-900' => $selectedDate->isSameDay($day),
                 '!bg-yellow-100 dark:!bg-yellow-900' => $trophy,
            ])
            wire:click="selectDate('{{ $day->format('Y-m-d') }}')"
        >
            @if($trophy)
                <flux:icon.trophy variant="micro" class="text-yellow-500 drop-shadow absolute -mt-5"/>
            @endif
            <div class="font-bold text-sm leading-none">{{ $day->format('d') }}</div>

            <div class="flex gap-1">
                @foreach($goals as $goal)
                    <div
                        @class([
                            'size-1 rounded-full',
                            'bg-lime-500' => $goal->reached,
                            'bg-red-300' => !$goal->reached,
                        ])
                    ></div>
                @endforeach
            </div>
        </flux:card>
    @endforeach
</div>
