<div>
    <form wire:submit="ask">
        <flux:input wire:model="question" placeholder="Frage"/>
    </form>
    <div class="mt-4" wire:stream="answer">
        {{ $answer }}
    </div>
</div>
