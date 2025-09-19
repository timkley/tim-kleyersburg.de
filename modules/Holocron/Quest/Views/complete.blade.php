<x-layouts.app>
    <x-slot:title>{{ $title ?? '' }}</x-slot>
    <x-slot:header></x-slot>

    <p class="text-center text-4xl font-medium my-20 max-w-lg leading-normal mx-auto">
        Du hast die Quest {{ $quest->name }} erfolgreich abgeschlossen!
    </p>
    <x-slot:footer></x-slot>
</x-layouts.app>
