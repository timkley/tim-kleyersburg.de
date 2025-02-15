<x-slot:title>Vokabeln lernen</x-slot>

<div>
    <x-heading tag="h2">Vokabeln</x-heading>

    <div class="space-y-6">
        <flux:button.group>
            <flux:button wire:click="startTest(50)">Test mit 50 Vokabeln</flux:button>
            <flux:button wire:click="startTest(75)">Test mit 75 Vokabeln</flux:button>
            <flux:button wire:click="startTest(100)">Test mit 100 Vokabeln</flux:button>
        </flux:button.group>

        <flux:table :paginate="$words">
            <flux:columns>
                <flux:column>Englisch</flux:column>
                <flux:column>Deutsch</flux:column>
                <flux:column>Score</flux:column>
                <flux:column>Angelegt am</flux:column>
            </flux:columns>
            <flux:rows>
                <flux:row>
                    <flux:cell>
                        <flux:input
                            class="min-w-32"
                            wire:model="english"
                            wire:keydown.enter="addWord"
                        />
                    </flux:cell>
                    <flux:cell>
                        <flux:input
                            class="min-w-32"
                            wire:model="german"
                            wire:keydown.enter="addWord"
                        />
                    </flux:cell>
                    <flux:cell>
                        <flux:button
                            type="submit"
                            wire:click="addWord"
                            >Vokabel hinzufügen
                        </flux:button>
                    </flux:cell>
                </flux:row>
                @foreach ($words as $word)
                    <livewire:holocron.school.components.vocabulary-word
                        :$word
                        :key="$word->id"
                    />
                @endforeach
            </flux:rows>
        </flux:table>
    </div>

    <flux:separator class="my-12" />

    <x-heading tag="h2">Deine Tests</x-heading>

    <div class="space-y-6">
        <flux:table>
            <flux:columns>
                <flux:column>Datum</flux:column>
                <flux:column>Vokabeln</flux:column>
                <flux:column>Fehler</flux:column>
                <flux:column></flux:column>
            </flux:columns>
            <flux:rows>
                @foreach ($tests as $test)
                    <flux:row>
                        <flux:cell>
                            <a
                                href="{{ route('holocron.school.vocabulary.test', $test->id) }}"
                                wire:navigate
                            >
                                {{ $test->updated_at->format('d.m.Y H:i') }}
                            </a>
                        </flux:cell>
                        <flux:cell>{{ $test->leftWords()->count() }} von {{ $test->words()->count() }} Vokabeln übrig</flux:cell>
                        <flux:cell
                            >{{ $test->error_count }} Fehler ({{ $test->words()->count() ? round(100 / $test->words()->count() * $test->error_count) : 0 }}&nbsp;%)</flux:cell
                        >
                        <flux:cell>
                            <div class="flex items-center gap-x-3">
                                <flux:badge color="{{ $test->finished ? 'lime' : '' }}">
                                    {{ $test->finished ? 'Fertig' : 'Im Gange' }}
                                </flux:badge>

                                @if (auth()->user()->isTim())
                                    <flux:button
                                        class="ml-auto"
                                        wire:click="deleteTest({{ $test->id }})"
                                        icon="trash"
                                        variant="danger"
                                        size="xs"
                                        square
                                    />
                                @endif
                            </div>
                        </flux:cell>
                    </flux:row>
                @endforeach
            </flux:rows>
        </flux:table>
    </div>
</div>
