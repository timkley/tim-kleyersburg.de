<x-slot:title>Vokabeltest ausdrucken</x-slot>


<div class="mx-auto max-w-3xl">
    <flux:radio.group wire:model.live="mode" variant="segmented" class="print:hidden">
        <flux:radio label="Zufällig" value="random" />
        <flux:radio label="🇩🇪 Deutsch" value="german" />
        <flux:radio label="🇬🇧 Englisch" value="english" />
    </flux:radio.group>

    <ul class="mt-4 divide-y-2 text-lg divide-black/50">
        @foreach($words as $word)
            @php
                $lottery = match ($mode) {
                    'random' => rand(0, 1),
                    'german' => 0,
                    'english' => 1,
                }
            @endphp

            <li class="py-3">
                {{ $lottery ? $word->german : $word->english }}
            </li>
        @endforeach
    </ul>
</div>
