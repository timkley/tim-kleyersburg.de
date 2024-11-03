<x-layouts.app>
    <x-slot:title>Articles</x-slot>

    <div class="mx-auto mt-16 max-w-3xl">
        <x-heading
            tag="h1"
            class="text-center"
        >
            Articles
        </x-heading>

        <x-articles.list
            class="mt-6"
            :$articles
        />

        <div class="mt-12">
            {{ $paginator->links() }}
        </div>
    </div>
</x-layouts.app>
