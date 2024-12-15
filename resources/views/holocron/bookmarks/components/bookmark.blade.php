@props(['bookmark'])
@php
    $parsedUrl = parse_url($bookmark->url);
    $cleanUrl = rtrim($parsedUrl['host'].($parsedUrl['path'] ?? ''), '/');
    $title = $bookmark->title ?? $cleanUrl;
    $base64Favicon = $bookmark->favicon ? 'data:image/x-icon;base64,'.base64_encode($bookmark->favicon) : null;
@endphp

<flux:card class="@container !p-4">
    <div class="flex flex-col gap-3 @sm:flex-row justify-between h-full">
        <div class="min-w-0">
            <p class="line-clamp-2 text-lg font-bold">{{ $title }}</p>
            <div class="space-y-4">
                @if ($bookmark->description)
                    <div class="line-clamp-2">{{ $bookmark->description }}</div>
                @endif

                <a href="{{ $bookmark->url }}" target="_blank"
                   class="inline-flex max-w-full items-center gap-1 line-clamp-1">
                    @if(!is_null($base64Favicon))
                        <img src="{{ $base64Favicon }}" alt="{{ $title }}" class="size-4"/>
                    @else
                        <flux:icon.arrow-top-right-on-square variant="micro"/>
                    @endif
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
                wire:target="recrawl"
                icon="arrow-path"
                size="sm"
                square
                tooltip="Neu crawlen"
            />
            <flux:button
                wire:click="$parent.delete({{ $bookmark->id }})"
                wire:target="$parent.delete({{ $bookmark->id }})"
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
