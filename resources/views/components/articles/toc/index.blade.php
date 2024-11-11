@props([
    'headings' => [],
])

<x-heading
    tag="h2"
    class="mt-8"
>
    Table of Contents
</x-heading>

<div {{ $attributes }}>
    <x-articles.toc.headings :headings="$headings" />
</div>
