@props([
    'markdown' => true,
])

@php
    $content = $markdown ? str($slot)->markdown() : $slot;
@endphp

<div {{ $attributes->class('not-prose rounded-md border-l-4 border-l-orange-300 bg-yellow-50 px-3 py-2 leading-snug dark:border-l-yellow-900 dark:bg-yellow-700 dark:text-white/90') }}>
    {!! $content !!}
</div>
