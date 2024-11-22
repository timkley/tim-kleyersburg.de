@seo([
    'title' => $frontmatter->title,
    'description' => $frontmatter->excerpt,
    'url' => route('prezet.show', ['slug' => $frontmatter->slug]),
    'image' => $frontmatter->image,
])
<x-slot:seo>
    <x-seo::meta />
</x-slot>

<div class="prose lg:prose-lg dark:prose-invert prose-headings:font-ibm prose-headings:font-semibold mx-auto mb-24 md:mt-8 lg:mt-16">
    <h1 class="[text-wrap:balance]">{{ $frontmatter->title }}</h1>
    <div class="not-prose mb-12">
        <div class="flex items-center">
            <div class="mr-2 h-6 w-6 shrink-0 overflow-hidden rounded-full">
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
                    @if ($frontmatter->updatedAt && ! $frontmatter->updatedAt->isSameDay($frontmatter->createdAt) && ! $frontmatter->updatedAt->lt($frontmatter->createdAt))
                        <span class="mx-1">•</span> last updated on {{ $frontmatter->updatedAt->format('F j, Y') }}
                    @endif

                    <span class="mx-1">•</span> {{ $minutesToRead }} to read
                </span>
            </div>
        </div>

        <x-articles.toc
            class="space-y-2"
            :headings="$headings"
        />
    </div>

    @if ($frontmatter->rambling)
        <flux:card class="not-prose p-4!">
            <p>
                You know when you sometimes get really agitated when writing or thinking about something? This was one of those times. Click the
                button to see what probably was going on inside my head.
            </p>
            <flux:button
                class="mt-4"
                wire:click="ramble"
                >Turn f-bomb filter {{ $rambling ? 'on' : 'off' }}</flux:button
            >
            <p class="mt-3 text-xs opacity-50">This uses AI.</p>
        </flux:card>
    @endif

    <div class="[&_img:hover]:max-w-full [&_img]:mx-auto [&_img]:max-w-sm">
        {!! $content !!}
    </div>

    <hr />

    @if (count($related))
        <p class="font-semibold">You might find these related articles helpful or interesting, make sure to check them out!</p>

        <x-articles.list :articles="$related" />
    @endif

    <p class="mt-16!">I hope this article helped you! If you have any questions, hit me up on <a href="https://x.com/timkley">X</a> 😊.</p>

    @env('local')
        <hr />
        <h3>Debugging</h3>
        <a href="{{ route('prezet.ogimage', $frontmatter->slug) }}">OG Image</a>
    @endenv
</div>
