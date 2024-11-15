<x-slot:title>Vokabeltest</x-slot>

@php
    $lottery = rand(0, 1);
@endphp

<div>
    @unless ($finished)
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

        <div class="mt-12 flex justify-center gap-4">
            <flux:button
                wire:click="markAsCorrect({{ $word->id }})"
                variant="primary"
                >Gewusst!</flux:button
            >
            <flux:button
                wire:click="markAsWrong({{ $word->id }})"
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
            >Zurück</flux:button
        >
    </div>
</div>
