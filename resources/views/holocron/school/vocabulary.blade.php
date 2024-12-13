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
            <flux:radio
                label="Score < 5"
                value="middle_score"
            />
            <flux:radio
                label="Score > 3"
                value="high_score"
            />
        </flux:radio.group>

        <flux:table>
            <flux:columns>
                <flux:column>Deutsch</flux:column>
                <flux:column>Englisch</flux:column>
                <flux:column>Score</flux:column>
                <flux:column>Angelegt am</flux:column>
            </flux:columns>
            <flux:rows>
                <flux:row>
                    <flux:cell>
                        <flux:input
                            class="min-w-32"
                            wire:model="german"
                            wire:keydown.enter="addWord"
                        />
                    </flux:cell>
                    <flux:cell>
                        <flux:input
                            class="min-w-32"
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
                    <livewire:holocron.school.components.vocabulary-word
                        :$word
                        :key="$word->id"
                    />
                @endforeach
            </flux:rows>
        </flux:table>

        {{ $words->links() }}

        <flux:button
            variant="primary"
            wire:click="startTest"
            >Test mit {{ $words->total() }} Vokabeln starten
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
                        <flux:cell>{{ $test->leftWords()->count() }} von {{ $test->words()->count() }} Vokabeln übrig</flux:cell>
                        <flux:cell>{{ $test->error_count }} Fehler</flux:cell>
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
