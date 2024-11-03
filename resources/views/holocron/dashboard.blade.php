<x-layouts.holocron>
    <x-slot:title>Holocron Dashboard</x-slot>

    <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
        @foreach ($cards as $card)
            <a
                class="border-0"
                href="{{ $card->link }}"
            >
                <flux:card class="hover:bg-white/75 dark:hover:bg-white/5">
                    <div>
                        <flux:heading
                            class="flex items-center gap-2 font-semibold"
                            size="lg"
                        >
                            <x-dynamic-component component="icon.{{ $card->icon }}" />
                            {{ $card->heading }}
                        </flux:heading>

                        <flux:subheading>
                            @foreach ($card->data as $data)
                                <p>{{ $data }}</p>
                            @endforeach
                        </flux:subheading>
                    </div>
                </flux:card>
            </a>
        @endforeach
    </div>
</x-layouts.holocron>
