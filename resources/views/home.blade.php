<x-layouts.app>
    <x-slot:metaDescription>
        I'm Tim, with a deep interest in the complex domains of web development, artificial intelligence, and automation.
    </x-slot>

    <div class="mx-auto mt-16 max-w-lg space-y-16 text-center sm:space-y-20 md:space-y-24">
        <div>
            <x-heading tag="h1">Hello there!</x-heading>
            <p class="[text-wrap:pretty] md:text-xl">
                I'm Tim. I have a deep interest in the complex domains of web development, artificial intelligence, and automation. Right now, I'm
                dedicated to streamlining and automating processes, and immerse myself in exploring these exciting fields.
            </p>
        </div>

        <livewire:home.last-scrobble />

        <div>
            <x-heading tag="h2">Latest articles</x-heading>

            <x-articles.list :$articles />

            <a
                class="mt-10 inline-block"
                href="{{ route('articles.index') }}"
                >all articles</a
            >
        </div>

        <div>
            <x-heading tag="h2">My career</x-heading>

            <ul class="space-y-10 divide-y divide-gray-300 text-left md:space-y-16 dark:divide-gray-700">
                @foreach ($cvItems as $item)
                    <li class="pt-10 first:pt-0 md:pt-16">
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $item['date'] }}</p>
                        <p class="mb-1 mt-2 text-xl font-bold dark:text-white">{{ $item['title'] }}</p>
                        <p class="mb-3 text-gray-600 dark:text-gray-400">
                            <a
                                href="{{ $item['link'] }}"
                                target="_blank"
                            >
                                {{ $item['employer'] }}
                            </a>
                        </p>
                        {{ $item['content'] }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <x-heading tag="h2">Where to find me online</x-heading>

            <ul class="space-y-3">
                @foreach ($contactItems as $item)
                    <li>
                        <a href="{{ $item['url'] }}">
                            {{ $item['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-layouts.app>
