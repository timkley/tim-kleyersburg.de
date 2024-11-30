@props(['tag' => 'div'])

<{{ $tag }}
    {{
        $attributes->class([
            'font-ibm mb-6 text-3xl font-semibold sm:mb-8 lg:text-4xl dark:text-white' => $tag === 'h1',
            'font-ibm mb-4 text-2xl font-semibold sm:mb-6 dark:text-white' => $tag === 'h2',
        ])
    }}
>
    {{ $slot }}
</{{ $tag }}>
