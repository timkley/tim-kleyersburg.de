@use(Illuminate\Support\Carbon)

<div class="grid grid-cols-5 sm:grid-cols-10 gap-3 mt-6 mb-8">
    @foreach($goalsByDay as $day => $goals)
        <flux:card class="flex flex-col gap-3 items-center !p-3">
            <div class="font-bold text-sm leading-none">{{ Carbon::parse($day)->format('d') }}</div>

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
