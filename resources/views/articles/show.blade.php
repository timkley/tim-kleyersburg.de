<x-layouts.app>
    @seo([
        'title' => $frontmatter->title,
        'description' => $frontmatter->excerpt,
        'url' => route('prezet.show', ['slug' => $frontmatter->slug]),
        'image' => $frontmatter->image,
    ])
    <x-slot:seo>
        <x-seo::meta />
    </x-slot>

    <div class="prose mx-auto mb-24 lg:prose-lg dark:prose-invert md:mt-8 lg:mt-16 prose-headings:font-ibm prose-headings:font-semibold">
        <h1 class="[text-wrap:balance]">{{ $frontmatter->title }}</h1>
        <div class="not-prose flex items-center">
            <div class="mr-2 h-6 w-6 flex-shrink-0 overflow-hidden rounded-full">
                <img
                    class="my-0"
                    src="/img/avatar_small.jpg"
                    alt="Tim Kleyersburg"
                />
            </div>
            <div>
                Tim Kleyersburg
                <span class="text-slate-500 dark:text-slate-400">
                    on {{ $frontmatter->createdAt->format('F j, Y') }}
                    @if ($frontmatter->updatedAt)
                        , last updated on {{ $frontmatter->updatedAt->format('F j, Y') }}
                    @endif
                </span>
            </div>
        </div>
        <div class="mt-1 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $minutesToRead }} to read</div>

        <div class="[&_img:hover]:max-w-full [&_img]:mx-auto [&_img]:max-w-sm">
            {!! $content !!}
        </div>

        <hr />

        <p class="font-semibold">You might find these related articles helpful or interesting, make sure to check them out!</p>

        <x-articles.list :articles="$related" />

        <p class="!mt-16">I hope this article helped you! If you have any questions, hit me up on <a href="https://x.com/timkley">X</a> 😊.</p>

        @env('local')
            <hr />
            <h3>Debugging</h3>
            <a href="{{ route('prezet.ogimage', $frontmatter->slug) }}">OG Image</a>
        @endenv
    </div>
</x-layouts.app>
