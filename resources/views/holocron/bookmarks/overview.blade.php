<x-slot:title>Lesezeichen</x-slot>

<div>
    <x-heading tag="h2">Lesezeichen</x-heading>

    <form
        class="max-w-sm"
        wire:submit="submit"
    >
        <flux:input.group>
            <flux:input
                wire:model="url"
                placeholder="URL"
            />

            <flux:button
                type="submit"
                icon="plus"
                >Speichern</flux:button
            >
        </flux:input.group>
    </form>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach ($bookmarks as $bookmark)
            <livewire:holocron.bookmarks.components.bookmark
                :$bookmark
                :key="$bookmark->id"
            />
        @endforeach
    </div>

    <flux:pagination :paginator="$bookmarks" />
</div>
