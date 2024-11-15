@props(['tag' => 'div'])

<{{ $tag }}
    {{
        $attributes->class([
            'mb-6 font-ibm text-3xl font-semibold sm:mb-8 lg:text-4xl dark:text-white' => $tag === 'h1',
            'mb-4 font-ibm text-2xl font-semibold sm:mb-6 dark:text-white' => $tag === 'h2',
        ])
    }}
>
    {{ $slot }}
</{{ $tag }}>
