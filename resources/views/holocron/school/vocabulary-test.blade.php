<x-slot:title>Vokabeltest</x-slot>

<div>
    @unless ($finished)
        <div class="grid grid-cols-2 gap-4">
            <flux:card class="grid place-content-center">
                <p class="text-center text-3xl">
                    {{ $word->german }}
                </p>
            </flux:card>
            <flux:card
                @click="$wire.blurred = false"
                class="grid place-content-center overflow-hidden"
            >
                <p
                    class="text-center text-3xl blur-lg"
                    :class="{ 'blur-lg': $wire.blurred }"
                >
                    {{ $word->english }}
                </p>
            </flux:card>
        </div>

        <div class="mt-12 flex justify-center gap-4">
            <flux:button
                wire:click="markAsCorrect({{ $word->id }})"
                variant="primary"
                >richtig</flux:button
            >
            <flux:button
                wire:click="markAsWrong({{ $word->id }})"
                variant="danger"
                >falsch</flux:button
            >
        </div>
    @else
        fertig
    @endif
</div>
