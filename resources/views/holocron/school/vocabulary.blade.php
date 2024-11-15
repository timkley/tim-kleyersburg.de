<x-slot:title>Vokabeln lernen</x-slot>

<div>
    Vokabeln
    <ul>
        <li>Noch nie gemacht (correct / wrong = 0)</li>
        <li>öfter falsch als richtig (wrong > correct)</li>
        <li>selten gemacht (wrong + correct < 5)</li>
    </ul>

    <x-heading tag="h2">Vokabeln</x-heading>

    <form wire:submit="addWord">
        <div class="flex gap-4">
            <flux:input
                wire:model="german"
                label="Deutsch"
            />
            <flux:input
                wire:model="english"
                label="Englisch"
            />
        </div>

        <flux:button
            class="mt-3"
            type="submit"
            >Vokabel hinzufügen</flux:button
        >
    </form>

    <flux:separator class="my-12" />

    <flux:table>
        <flux:columns>
            <flux:column>Deutsch</flux:column>
            <flux:column>Englisch</flux:column>
            <flux:column>Angelegt am</flux:column>
        </flux:columns>
        <flux:rows>
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

    <flux:button
        variant="primary"
        wire:click="startTest"
        >Test starten</flux:button
    >

    {{ $words->links() }}

    <flux:separator class="my-12" />

    <x-heading tag="h2">Vergangene Tests</x-heading>

    <flux:table>
        <flux:columns>
            <flux:column>Datum</flux:column>
            <flux:column>Vokabeln</flux:column>
            <flux:column>Fehler</flux:column>
        </flux:columns>
        <flux:rows>
            @foreach ($tests as $test)
                <flux:row>
                    <flux:cell>{{ $test->updated_at->format('d.m.Y H:i') }}</flux:cell>
                    <flux:cell>{{ $test->word_ids->count() }} Vokabeln</flux:cell>
                    <flux:cell>{{ $test->error_count }} Fehler</flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>
</div>
