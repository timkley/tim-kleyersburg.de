{{-- Credit: Lucide (https://lucide.dev) --}}

@props([
    'variant' => 'outline',
])

@php
if ($variant === 'solid') {
    throw new \Exception('The "solid" variant is not supported in Lucide.');
}

$classes = Flux::classes('shrink-0')
    ->add(match($variant) {
        'outline' => '[:where(&)]:size-6',
        'solid' => '[:where(&)]:size-6',
        'mini' => '[:where(&)]:size-5',
        'micro' => '[:where(&)]:size-4',
    });

$strokeWidth = match ($variant) {
    'outline' => 2,
    'mini' => 2.25,
    'micro' => 2.5,
};
@endphp

<svg
    {{ $attributes->class($classes) }}
    data-flux-icon
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    data-slot="icon"
>
  <path d="M10.1 2.18a9.93 9.93 0 0 1 3.8 0" />
  <path d="M17.6 3.71a9.95 9.95 0 0 1 2.69 2.7" />
  <path d="M21.82 10.1a9.93 9.93 0 0 1 0 3.8" />
  <path d="M20.29 17.6a9.95 9.95 0 0 1-2.7 2.69" />
  <path d="M13.9 21.82a9.94 9.94 0 0 1-3.8 0" />
  <path d="M6.4 20.29a9.95 9.95 0 0 1-2.69-2.7" />
  <path d="M2.18 13.9a9.93 9.93 0 0 1 0-3.8" />
  <path d="M3.71 6.4a9.95 9.95 0 0 1 2.7-2.69" />
  <circle cx="12" cy="12" r="1" />
</svg>
