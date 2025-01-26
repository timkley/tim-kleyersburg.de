<x-slot:title>Vokabeltest</x-slot>

@php
    $lottery = rand(0, 1);
@endphp

<div class="mx-auto max-w-3xl">
    @unless ($finished || $word === null)
        <div class="grid grid-cols-2 gap-4">
            <flux:card class="grid place-content-center">
                <p class="break-words text-center text-xl sm:text-3xl">
                    {{ $lottery ? $word->german : $word->english }}
                </p>
            </flux:card>
            <flux:card
                @click="$wire.blurred = false"
                class="grid place-content-center overflow-hidden"
            >
                <p
                    class="break-words text-center text-xl blur-lg sm:text-3xl"
                    :class="{ 'blur-lg': $wire.blurred }"
                >
                    {{ $lottery ? $word->english : $word->german }}
                </p>
            </flux:card>
        </div>

        <div class="mt-6 text-center text-gray-500">noch {{ $test->leftWords()->count() }} Vokabeln übrig</div>

        <div class="mt-12 flex justify-center gap-4">
            <flux:button
                wire:click="markAsCorrect({{ $word->id }}); right.play()"
                variant="primary"
                >Gewusst!</flux:button
            >
            <flux:button
                wire:click="markAsWrong({{ $word->id }}); wrong.play()"
                variant="danger"
                >Wiederholen</flux:button
            >
        </div>
    @else
        <p class="text-center text-2xl">Geschafft! 🥳</p>
    @endif

    <flux:separator class="my-12" />

    <div class="text-center">
        <flux:button
            href="{{ route('holocron.school.vocabulary.overview') }}"
            variant="filled"
            class="mx-auto"
            wire:navigate
            >Zurück</flux:button
        >
    </div>

    @persist('audio-files')
        <div class="hidden">
            <audio
                id="right"
                src="/sounds/right.mp3"
                controls
            ></audio>
            <audio
                id="wrong"
                src="/sounds/wrong.mp3"
                controls
            ></audio>
        </div>
    @endpersist
</div>
