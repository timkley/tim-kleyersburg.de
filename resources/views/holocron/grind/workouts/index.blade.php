<div class="space-y-8">
    @include('holocron.grind.navigation')

    <div class="space-y-1">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($plans as $plan)
                <flux:card
                    size="sm"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-700 space-y-2"
                    wire:click="start({{ $plan->id }})"
                >
                    <flux:text>
                        {{ $plan->name }}
                    </flux:text>
                    <flux:heading class="flex items-center gap-x-1">
                        @if($plan->exercises->count())
                            <flux:icon variant="micro" icon="rocket-launch"></flux:icon>
                            Workout starten
                        @else
                            Plan enthält keine Übungen
                        @endif
                        </flux:heading>
                </flux:card>
            @endforeach
        </div>
    </div>

    @if($unfinishedWorkouts->count())
        <div class="space-y-4">
            <flux:heading size="lg">
                Angefangene Workouts
            </flux:heading>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($unfinishedWorkouts as $unfinishedWorkout)
                    <a href="{{ route('holocron.grind.workouts.show', $unfinishedWorkout->id) }}" wire:navigate>
                        <flux:card
                            size="sm"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-700 space-y-2"
                            wire:click="start({{ $plan->id }})"
                        >
                            <flux:text>
                                {{ $unfinishedWorkout->plan->name }}
                            </flux:text>
                            <flux:heading class="flex items-center gap-x-1">
                                {{ $unfinishedWorkout->started_at->format('d.m., H:i') }} Uhr
                            </flux:heading>
                        </flux:card>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="space-y-4">
        <flux:heading size="lg">
            Vergangene Workouts
            <flux:badge size="sm" class="ml-2">{{ $allWorkoutsCount }}</flux:badge>
        </flux:heading>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($pastWorkouts as $pastWorkout)
                <a href="{{ route('holocron.grind.workouts.show', $pastWorkout->id) }}" wire:navigate>
                    <flux:card
                        size="sm"
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-700 space-y-2"
                        wire:click="start({{ $plan->id }})"
                    >
                        <flux:text>
                            {{ $pastWorkout->plan->name }}
                        </flux:text>
                        <flux:heading class="flex items-center gap-x-1">
                            @php
                                echo implode(', ', [
                                    $pastWorkout->started_at->format('d.m., H:i'),
                                    $pastWorkout->finished_at ? $pastWorkout->started_at->diffInMinutes($pastWorkout->finished_at) . ' Min' : null])
                            @endphp
                        </flux:heading>
                    </flux:card>
                </a>
            @endforeach
        </div>

        <flux:pagination :paginator="$pastWorkouts" />
    </div>
</div>
