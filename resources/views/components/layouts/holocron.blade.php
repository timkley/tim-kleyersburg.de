@php use App\Models\Holocron\Grind\Workout; @endphp
<x-layouts.app>
    <x-slot:title>{{ $title ?? '' }}</x-slot>
    <x-slot:header>
        <div class="mx-auto max-w-5xl print:hidden text-sm flex items-center gap-x-6">
            <a
                href="{{ route('holocron.dashboard') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 !border-0 font-mono font-semibold"
            >
                <flux:icon.cpu-chip />
                <span> Holocron </span>
            </a>
        </div>
    </x-slot>

    <div class="mx-auto mt-6 max-w-5xl mb-24">
        {{ $slot }}

        <div class="fixed px-4 py-3 bg-white/90 dark:bg-slate-700/80 backdrop-blur-lg shadow shadow-blue-400/30 border border-black/10 w-fit bottom-10 inset-x-0 mx-auto rounded-full">
            <div class="flex gap-x-3">
                <a
                    href="{{ route('holocron.dashboard') }}"
                    wire:navigate
                    wire:current="font-semibold"
                >Home</a>

                <a
                    href="{{ route('holocron.quests') }}"
                    wire:navigate
                    wire:current="font-semibold"
                >Quests</a>

                <a
                    href="{{ route('holocron.grind') }}"
                    wire:navigate
                    wire:current="font-semibold"
                >Grind</a>

                @if($workout = Workout::query()->whereNotNull('started_at')->whereNull('finished_at')->limit(1)->first())
                    <flux:separator vertical />

                    <a href="{{ route('holocron.grind.workouts.show', $workout->id) }}" class="animate-rotate-wiggle">
                        <flux:icon icon="biceps-flexed" />
                    </a>
                @endif
            </div>
        </div>
    </div>

    <x-slot:footer></x-slot>

    @persist('toast')
        <flux:toast />
    @endpersist
</x-layouts.app>
