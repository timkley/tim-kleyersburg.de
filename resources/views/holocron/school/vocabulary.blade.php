<x-slot:title>Vokabeln lernen</x-slot>

<div>
    <x-heading tag="h2">Vokabeln</x-heading>

    <flux:table>
        <flux:columns>
            <flux:column>Deutsch</flux:column>
            <flux:column>Englisch</flux:column>
            <flux:column>Angelegt am</flux:column>
        </flux:columns>
        <flux:rows>
            <flux:row>
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
                        >Vokabel hinzufügen</flux:button
                    >
                </flux:cell>
            </flux:row>
            @foreach ($words as $word)
                <flux:row>
                    <flux:cell>{{ $word->german }}</flux:cell>
                    <flux:cell>{{ $word->english }}</flux:cell>
                    <flux:cell>{{ $word->created_at->format('d.m.Y H:i') }}</flux:cell>
                    <flux:cell>
                        <flux:button
                            wire:click="deleteWord({{ $word->id }})"
                            square
                        >
                            <flux:icon.trash variant="mini" />
                        </flux:button>
                    </flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>

    <div class="mt-6">
        {{ $words->links() }}
    </div>

    <flux:button
        class="my-6"
        variant="primary"
        wire:click="startTest"
        >Test starten
    </flux:button>

    <flux:separator class="my-12" />

    <x-heading tag="h2">Deine Tests</x-heading>

    <flux:table>
        <flux:columns>
            <flux:column>Datum</flux:column>
            <flux:column>Vokabeln</flux:column>
            <flux:column>Fehler</flux:column>
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
                    <flux:cell>{{ $test->leftWords()->count() }} / {{ $test->word_ids->count() }} Vokabeln</flux:cell>
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
