<div>
    <flux:modal
        name="command-modal"
        class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden px-4">
        <flux:heading size="lg">Quest anlegen</flux:heading>

        <form wire:submit="submit" class="space-y-4 mt-4">
            <flux:input autofocus wire:model="name" placeholder="Neue Quest erstellen..." />

            <flux:switch wire:model="should_be_printed" label="Ausdrucken" icon="print" align="left"/>

            <flux:radio.group wire:model="date" variant="buttons" class="w-full *:flex-1">
                <flux:radio :value="today()->toDateString()">Heute</flux:radio>
                <flux:radio :value="today()->addDay()->toDateString()">Morgen</flux:radio>
                <flux:radio :value="today()->addDays(2)->toDateString()">Übermorgen</flux:radio>
            </flux:radio.group>

            <flux:button variant="primary" type="submit">Quest anlegen</flux:button>
        </form>
    </flux:modal>
</div>
