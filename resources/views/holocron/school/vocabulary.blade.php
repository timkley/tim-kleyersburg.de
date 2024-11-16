<x-slot:title>Vokabeln lernen</x-slot>

<div>
    <x-heading tag="h2">Vokabeln</x-heading>

    <div class="space-y-6">
        <flux:radio.group
            wire:model.live="filter"
            variant="segmented"
        >
            <flux:radio
                checked
                label="Alle"
                value="all"
            />
            <flux:radio
                label="Score < 3"
                value="low_score"
            />
        </flux:radio.group>

        <flux:checkbox.group wire:model.self="checkedWords">
            <flux:table>
                <flux:columns>
                    <flux:column>
                        <flux:checkbox.all />
                    </flux:column>
                    <flux:column>Deutsch</flux:column>
                    <flux:column>Englisch</flux:column>
                    <flux:column>Score</flux:column>
                    <flux:column>Angelegt am</flux:column>
                </flux:columns>
                <flux:rows>
                    <flux:row>
                        <flux:cell></flux:cell>
                        <flux:cell>
                            <flux:input
                                wire:model="german"
                                wire:keydown.enter="addWord"
                            />
                        </flux:cell>
                        <flux:cell>
                            <flux:input
                                wire:model="english"
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
                        <livewire:holocron.school.vocabulary-word
                            :$word
                            :key="$word->id"
                        />
                    @endforeach
                </flux:rows>
            </flux:table>
        </flux:checkbox.group>

        <flux:button
            variant="primary"
            wire:click="startTest"
            >Test starten
        </flux:button>
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
                        <flux:cell>{{ $test->leftWords()->count() }} / {{ $test->word_ids->count() }}Vokabeln </flux:cell>
                        <flux:cell>{{ $test->error_count }} Fehler</flux:cell>
                        <flux:cell>
                            <flux:badge color="{{ $test->finished ? 'lime' : '' }}">
                                {{ $test->finished ? 'Fertig' : 'Im Gange' }}
                            </flux:badge>
                        </flux:cell>
                    </flux:row>
                @endforeach
            </flux:rows>
        </flux:table>
    </div>
</div>
