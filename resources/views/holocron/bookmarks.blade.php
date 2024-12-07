<div>
    <form class="max-w-sm" wire:submit="submit">
        <flux:input.group>
            <flux:input wire:model="url" placeholder="URL"/>

            <flux:button type="submit" icon="plus">Speichern</flux:button>
        </flux:input.group>
    </form>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach($bookmarks as $bookmark)
            @php
                $parsedUrl = parse_url($bookmark->url);
                $cleanUrl = rtrim($parsedUrl['host'] . $parsedUrl['path'], '/');
                $title = $bookmark->title ?? $cleanUrl;
                $base64Favicon = $bookmark->favicon ? 'data:image/x-icon;base64,' . base64_encode($bookmark->favicon) : null;
            @endphp

            <flux:card class="flex justify-between">
                <div>
                    <p class="text-lg font-bold">{{ $title }}</p>
                    <div class="space-y-4">
                        @if($bookmark->description)
                            <div>{{ $bookmark->description }}</div>
                        @endif

                        <a href="{{ $bookmark->url }}" target="_blank" class="inline-flex items-center gap-1">
                            @if($base64Favicon)
                                <img src="{{ $base64Favicon }}" alt="{{ $title }}" class="size-4"/>
                            @else
                                <flux:icon.arrow-top-right-on-square variant="micro"/>
                            @endif
                            {{ $cleanUrl }}
                        </a>

                        @if($bookmark->summary)
                            <div class="text-sm">{{ $bookmark->summary }}</div>
                        @endif
                    </div>
                </div>
                <flux:button.group>
                    <flux:button
                        wire:click="recrawl({{ $bookmark->id }})"
                        icon="arrow-path"
                        size="sm"
                        square
                    />
                    <flux:button
                        wire:click="deleteTest({{ $bookmark->id }})"
                        wire:confirm="Willste sicher löschen?"
                        icon="trash"
                        variant="danger"
                        size="sm"
                        square
                    />
                </flux:button.group>

            </flux:card>
        @endforeach
    </div>

    {{ $bookmarks->links() }}
</div>
