@use(App\Models\Holocron\Bookmark)

<a
    data-flux-button
    wire:navigate
    href="{{ route('holocron.bookmarks') }}"
>
    <flux:card class="h-full hover:bg-white/75 dark:hover:bg-white/5">
        <div>
            <flux:heading
                class="flex items-center gap-2 font-semibold"
                size="lg"
            >
                <flux:icon.link />
                Lesezeichen
            </flux:heading>
            <flux:subheading> {{ Bookmark::count() }} Lesezeichen</flux:subheading>
        </div>
    </flux:card>
</a>
