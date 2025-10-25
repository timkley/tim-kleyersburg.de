<flux:button
    icon="printer"
    variant="filled"
    size="sm"
    wire:click="toggle"
>
    <div class="flex items-center gap-2">
        @if(auth()->user()?->settings?->printer_silenced)
            Drucker stumm

            <div class="size-2 bg-red-600 rounded-full"></div>
        @else
            Drucker aktiv

            <div class="size-2 bg-green-600 rounded-full"></div>
        @endif
    </div>
</flux:button>
