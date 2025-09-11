<div>
    <flux:card class="space-y-4">
        @if($notes->isNotEmpty())
            <div class="space-y-2" wire:key="main-notes">
                <flux:heading>Notizen</flux:heading>
                @foreach($notes as $quest)
                    <livewire:holocron.quest.components.item
                        :$quest
                        :key="'main-note.' . $quest->id"
                    />
                @endforeach
            </div>
        @endif
    </flux:card>
</div>
