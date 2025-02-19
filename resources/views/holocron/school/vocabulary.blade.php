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
            <flux:table.columns>
                <flux:table.column>Englisch</flux:table.column>
                <flux:table.column>Deutsch</flux:table.column>
                <flux:table.column>Score</flux:table.column>
                <flux:table.column>Angelegt am</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                <flux:table.row>
                    <flux:table.cell>
                        <flux:input
                            class="min-w-32"
                            wire:model="english"
                            wire:keydown.enter="addWord"
                        />
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:input
                            class="min-w-32"
                            wire:model="german"
                            wire:keydown.enter="addWord"
                        />
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button
                            type="submit"
                            wire:click="addWord"
                            >Vokabel hinzufügen
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
                @foreach ($words as $word)
                    <livewire:holocron.school.components.vocabulary-word
                        :$word
                        :key="$word->id"
                    />
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:separator class="my-12" />

    <x-heading tag="h2">Deine Tests</x-heading>

    <div class="space-y-6">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Datum</flux:table.column>
                <flux:table.column>Vokabeln</flux:table.column>
                <flux:table.column>Fehler</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($tests as $test)
                    <flux:table.row>
                        <flux:table.cell>
                            <a
                                href="{{ route('holocron.school.vocabulary.test', $test->id) }}"
                                wire:navigate
                            >
                                {{ $test->updated_at->format('d.m.Y H:i') }}
                            </a>
                        </flux:table.cell>
                        <flux:table.cell>{{ $test->leftWords()->count() }} von {{ $test->words()->count() }} Vokabeln übrig</flux:table.cell>
                        <flux:table.cell
                            >{{ $test->error_count }} Fehler ({{ $test->words()->count() ? round(100 / $test->words()->count() * $test->error_count) : 0 }}&nbsp;%)</flux:table.cell
                        >
                        <flux:table.cell>
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
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>
