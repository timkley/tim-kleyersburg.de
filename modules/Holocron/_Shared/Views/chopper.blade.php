<div>
    <form wire:submit="ask">
        <flux:input wire:model="question" placeholder="Frage"/>
    </form>
    <div class="mt-4" wire:stream="answer">
        {{ str($answer)->markdown() }}
    </div>

    @if($context)
        <flux:accordion class="max-w-xl mt-8">
            <flux:accordion.item>
                <flux:accordion.heading>Kontext</flux:accordion.heading>
                <flux:accordion.content>
                    {{ $context }}
                </flux:accordion.content>
            </flux:accordion.item>
        </flux:accordion>
    @endif
</div>
