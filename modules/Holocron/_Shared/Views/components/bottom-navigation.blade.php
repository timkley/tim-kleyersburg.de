@use(Modules\Holocron\Grind\Models\Workout)

<div class="fixed bottom-6 inset-x-0 mx-auto w-fit flex flex-col items-center gap-1">
    <div class="px-5 py-3 bg-white/90 dark:bg-slate-700/80 backdrop-blur-lg shadow shadow-blue-400/30 border border-black/10 w-fit rounded-full">
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


            <flux:separator vertical/>

            <flux:dropdown position="top" align="start">
                <button>
                    <flux:icon icon="bars-3" class="mx-auto" variant="mini"/>
                    Mehr
                </button>

                <flux:navmenu>
                    <flux:navmenu.item href="{{ route('holocron.grind') }}" icon="dumbbell">Grind</flux:navmenu.item>
                    <flux:navmenu.item href="{{ route('holocron.gear') }}" icon="shopping-bag">Gear</flux:navmenu.item>
                    <flux:navmenu.item href="{{ route('holocron.chopper') }}" icon="sparkles">Chopper</flux:navmenu.item>
                </flux:navmenu>
            </flux:dropdown>

            <flux:modal.trigger name="search-modal" shortcut="f">
                <button>
                    <flux:icon icon="magnifying-glass" class="mx-auto" variant="mini"/>
                    Suche
                </button>
            </flux:modal.trigger>

            <flux:modal.trigger name="command-modal" shortcut="cmd.k">
                <button>
                    <flux:icon icon="plus" class="mx-auto" variant="mini"/>
                    Neu
                </button>
            </flux:modal.trigger>

            @if($workout = Workout::query()->whereNotNull('started_at')->whereNull('finished_at')->limit(1)->first())
                <flux:separator vertical/>

                <a href="{{ route('holocron.grind.workouts.show', $workout->id) }}"
                   class="animate-rotate-wiggle self-center">
                    <flux:icon icon="biceps-flexed"/>
                </a>
            @endif
        </div>
    </div>
</div>
