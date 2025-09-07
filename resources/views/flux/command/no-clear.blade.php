@pure

@props([
    'placeholder' => null,
])

@php
    $classes = Flux::classes()
        ->add('w-full block rounded-xl overflow-hidden shadow-xs')
        ->add('border border-zinc-200 dark:border-zinc-600')
        ;
@endphp

<ui-select {{ $attributes->class($classes)->merge(['filter' => true]) }} data-flux-command>
    {{ $slot }}
</ui-select>
