@props(['bookmark'])

<flux:card class="@container !p-4">
    <div class="flex flex-col gap-3 @sm:flex-row justify-between h-full">
        <div class="min-w-0 flex-1">
            <textarea class="outline-0 -ml-2 w-full resize-none rounded py-1 px-2 text-lg font-bold -mt-0.5 line-clamp-2 field-sizing-content focus:line-clamp-none hover:bg-black/3 focus:bg-black/5 dark:focus:bg-white/10" wire:model.live="title">{{ $title }}</textarea>
            <div class="space-y-4">
                @if ($bookmark->description)
                    <textarea class="outline-0 -ml-2 w-full resize-none rounded py-1 px-2 mt-0.5 line-clamp-2 field-sizing-content focus:line-clamp-none hover:bg-black/3 focus:bg-black/5 dark:focus:bg-white/10" wire:model.live="description">{{ $description }}</textarea>
                @endif

                <a href="{{ $bookmark->url }}" target="_blank"
                   class="inline-flex max-w-full items-center gap-1 line-clamp-1">
                    @unless(is_null($base64Favicon))
                        <img src="{{ $base64Favicon }}" alt="{{ $title }}" class="size-4"/>
                    @else
                        <flux:icon.arrow-top-right-on-square variant="micro"/>
                    @endunless
                    <span class="truncate">{{ $cleanUrl }}</span>
                </a>

                @if ($bookmark->summary)
                    <div class="text-sm">{{ $bookmark->summary }}</div>
                @endif
            </div>
        </div>

        <flux:button.group>
            <flux:button
                wire:click="recrawl"
                icon="arrow-path"
                size="sm"
                square
                tooltip="Neu crawlen"
            />
            <flux:button
                wire:click="$parent.delete({{ $bookmark->id }})"
                wire:confirm="Willst du das Lesezeichen wirklich löschen?"
                icon="trash"
                variant="danger"
                size="sm"
                square
                tooltip="Löschen"
            />
        </flux:button.group>
    </div>
</flux:card>
