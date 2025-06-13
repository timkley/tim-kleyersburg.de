<div>
    <div class="space-y-6">
        <div class="flex overflow-x-auto pb-2 scrollbar -mx-6">
            <div class="flex grow px-6 gap-x-4">
                @foreach($workoutExercises as $workoutExercise)
                    <div
                        @class([
                            'w-32 bg-black/5 dark:bg-white/10 rounded-lg flex-shrink-0 p-3 hyphens-auto flex flex-col justify-between gap-y-2 scroll-mx-4',
                            '!bg-sky-200 dark:!bg-sky-900' => $workoutExercise->id === $currentExercise->id,
                            'opacity-50' => $workoutExercise->sets()->whereNotNull('finished_at')->count() === $workoutExercise->sets
                        ])
                        @if($workoutExercise->id === $currentExercise->id)
                            data-current
                        @endif
                        wire:click="setExercise({{ $workoutExercise->id }})"
                    >
                        <div class="font-semibold">
                            {{ $workoutExercise->exercise->name }}
                        </div>
                        <div>
                            <div class="text-xs flex items-center gap-x-0.5">
                                <span>
                                    {{ $workoutExercise->min_reps }}
                                </span>
                                <flux:icon name="arrow-long-right" variant="micro"/>
                                <span>
                                    {{ $workoutExercise->max_reps }}&nbsp;Wdh.
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-[calc(var(--spacing)*5)_100px_125px_1fr] gap-x-3 gap-y-3">
            <div class="grid grid-cols-subgrid col-span-4 text-sm text-center">
                <div></div>
                <div>kg</div>
                <div>Wdh.</div>
            </div>
            @foreach($currentExercise->sets()->get() as $set)
                <livewire:holocron.grind.workouts.set
                    :$set
                    :min-reps="$currentExercise->min_reps"
                    :max-reps="$currentExercise->max_reps"
                    :key="$set->id"
                    :iteration="$loop->iteration"/>
            @endforeach

            <form wire:submit="recordSet" class="grid grid-cols-subgrid col-span-4">
                <div></div>
                <flux:input wire:model="weight" placeholder="Gewicht"/>
                <flux:input wire:model="reps" placeholder="Wdh."/>
                <flux:button type="submit" icon="arrow-right"/>
            </form>
        </div>
        @if(!$workout->finished_at)
            <livewire:holocron.grind.workouts.timer :$workout/>
        @endif

        <flux:separator class="mt-4"/>

        <div class="flex justify-center">
            @if(!$workout->finished_at)
                <flux:button class="mx-auto" icon="check-badge" variant="primary" wire:click="finish">Workout
                    abschließen
                </flux:button>
            @else
                <flux:text class="text-base">
                    Workout abgeschlossen 🚀
                </flux:text>
            @endif
        </div>
    </div>
</div>

@script
<script>
    const scrollIntoView = (el) => {
        el.scrollIntoView({
            behavior: 'smooth',
            block: 'left',
        })
    }

    scrollIntoView(document.querySelector('[data-current]'))

    Livewire.hook('morphed', () => {
        scrollIntoView(document.querySelector('[data-current]'))
    })
</script>
@endscript
