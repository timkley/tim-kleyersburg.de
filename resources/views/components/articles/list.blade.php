@props(['articles'])

<ul {{ $attributes->class('not-prose space-y-4 text-left') }}>
    @foreach ($articles as $article)
        <li class="flex items-baseline justify-between gap-2">
            <div>
                <a href="{{ route('prezet.show', $article->slug) }}">{{ $article->title }}</a>
            </div>
            <time
                class="ml-2 whitespace-nowrap text-sm tracking-tight text-gray-600 dark:text-gray-400"
                datetime="{{ $article->createdAt->toIso8601String() }}"
            >
                {{ $article->createdAt->format('F j, Y') }}
            </time>
        </li>
    @endforeach
</ul>
