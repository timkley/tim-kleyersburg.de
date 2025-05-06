<div>
    <div class="space-y-6">
        <div class="flex overflow-x-auto pb-2 scrollbar -mx-6"
             x-init="$el.querySelector('.\\!bg-sky-200').scrollIntoView({ block: 'nearest' })">
            <div class="flex grow px-6 gap-x-4">
                @foreach($workout->plan->exercises as $index => $exercise)
                    <div
                        @class([
                            'w-32 bg-black/5 dark:bg-white/10 rounded-lg flex-shrink-0 p-3 hyphens-auto flex flex-col justify-between gap-y-2',
                            '!bg-sky-200 dark:!bg-sky-900' => $exercise->id === $currentExercise->id
                        ])
                        wire:click="setExercise({{ $index }})"
                    >
                        <div class="font-semibold">
                            {{ $exercise->name }}
                        </div>
                        <div>
                            <div class="text-xs flex items-center gap-x-0.5">
                                <span>
                                    {{ $exercise->pivot->min_reps }}
                                </span>
                                <flux:icon name="arrow-long-right" variant="micro"/>
                                <span>
                                    {{ $exercise->pivot->max_reps }}&nbsp;Wdh.
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-[calc(var(--spacing)*6)_110px_125px_1fr] gap-4 gap-y-3">
            <div class="grid grid-cols-subgrid col-span-4 text-sm text-center">
                <div class="font-mono">#</div>
                <div>kg</div>
                <div>Wdh.</div>
            </div>
            @foreach($workout->sets()->where('exercise_id', $currentExercise->id)->get() as $set)
                <livewire:holocron.grind.workouts.set :$set
                                                      :min-reps="$currentExercise->pivot->min_reps"
                                                      :max-reps="$currentExercise->pivot->max_reps"
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

        <flux:separator class="mt-4"/>

        @if(!$workout->finished_at)
            <flux:button icon="check-badge" variant="primary" wire:click="finish">Workout abschließen</flux:button>
        @else
            Workout abgeschlossen
        @endif

        <livewire:holocron.grind.workouts.timer :$workout/>
    </div>
</div>
