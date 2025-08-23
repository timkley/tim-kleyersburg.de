@use(Modules\Holocron\Grind\Models\Workout)

<div class="fixed px-5 py-3 bg-white/90 dark:bg-slate-700/80 backdrop-blur-lg shadow shadow-blue-400/30 border border-black/10 w-fit bottom-10 inset-x-0 mx-auto rounded-full">
    <div class="flex gap-x-4 text-sm">
        <a
            href="{{ route('holocron.dashboard') }}"
            wire:navigate
            wire:current="font-semibold"
        >
            <flux:icon icon="home" class="mx-auto" variant="mini"/>
            Home
        </a>

        <a
            href="{{ route('holocron.quests') }}"
            wire:navigate
            wire:current="font-semibold"
        >
            <flux:icon icon="book-check" class="mx-auto" variant="mini"/>
            Quests
        </a>

        <a
            href="{{ route('holocron.grind') }}"
            wire:navigate
            wire:current="font-semibold"
        >
            <flux:icon icon="dumbbell" class="mx-auto" variant="mini"/>
            Grind
        </a>
        <a
            href="{{ route('holocron.gear') }}"
            wire:navigate
            wire:current="font-semibold"
        >
            <flux:icon icon="shopping-bag" class="mx-auto" variant="mini"/>
            Gear
        </a>
        <flux:separator vertical/>
        <a
            href="{{ route('holocron.chopper') }}"
            wire:navigate
            wire:current="font-semibold"
        >
            <flux:icon icon="sparkles" class="mx-auto" variant="mini"/>
            Chopper
        </a>

        @if($workout = Workout::query()->whereNotNull('started_at')->whereNull('finished_at')->limit(1)->first())
            <flux:separator vertical/>

            <a href="{{ route('holocron.grind.workouts.show', $workout->id) }}"
               class="animate-rotate-wiggle self-center">
                <flux:icon icon="biceps-flexed"/>
            </a>
        @endif
    </div>
</div>
