<div>
    <flux:modal
        name="command-modal"
        class="w-full max-w-[30rem] my-[12vh] max-h-screen overflow-y-hidden px-4"
    >
        <div @keydown.enter.prevent="$wire.submit($event.shiftKey)">
            <flux:heading size="lg">Quest anlegen</flux:heading>

            <form @submit.prevent class="space-y-4 mt-4">
                <flux:input autofocus wire:model="name" placeholder="Neue Quest erstellen..." />

                <flux:switch wire:model="should_be_printed" label="Ausdrucken" icon="print" align="left"/>

                <flux:radio.group wire:model="date" variant="buttons" class="w-full *:flex-1">
                    <flux:radio :value="today()->toDateString()">Heute</flux:radio>
                    <flux:radio :value="today()->addDay()->toDateString()">Morgen</flux:radio>
                    <flux:radio :value="today()->addDays(2)->toDateString()">Übermorgen</flux:radio>
                </flux:radio.group>

                <div class="flex gap-2">
                    <flux:button type="button" variant="primary" wire:click="submit(false)">Quest anlegen</flux:button>
                    <flux:button type="button" wire:click="submit(true)">Anlegen & neu</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
