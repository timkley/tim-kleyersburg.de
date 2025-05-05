<!DOCTYPE html>
<html lang="de" class="scroll-smooth">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
    <meta name="mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    @if (isset($seo))
        {!! $seo !!}
    @else
        <meta name="description" content="{{ $metaDescription ?? '' }}"/>
        <title>{{ $title ?? config('app.name') }}</title>
    @endif

    @livewireStyles
    @fluxAppearance
    @vite('resources/css/app.css')

    <link rel="preconnect" href="https://fonts.bunny.net"/>
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:600|inter:400,500,600" rel="stylesheet"/>

    @include('components.layouts.favicon')
</head>
@section('body')
    <body class="bg-linear-to-tr from-blue-700 to-blue-300 dark:from-blue-700 dark:to-blue-900 flex flex-col p-2 sm:p-3 md:p-4 min-h-svh font-sans {{ $additionalBodyClasses ?? '' }}">
    <div class="h-lvh flex-1 rounded-sm bg-sky-50 px-6 py-4 text-slate-900 selection:bg-blue-200 sm:rounded-md md:rounded-lg dark:bg-slate-800 dark:text-slate-300">
        @isset($header)
            {{ $header }}
        @else
            <header class="mx-auto mb-8 flex max-w-3xl items-center justify-between">
                <x-logo/>

                <a
                    href="{{ route('articles.index') }}"
                    class="decorated"
                    wire:navigate
                >Articles</a>
            </header>
        @endisset

        {{ $slot }}

        @isset($footer)
            {{ $footer }}
        @else
            <footer class="mx-auto mt-12 max-w-3xl">
                <div class="flex flex-col items-center justify-center gap-3 text-sm sm:flex-row sm:space-y-0">
                    <a
                        class="inline-flex gap-1 items-center text-slate-600 dark:text-slate-300"
                        href="https://github.com/timkley/tim-kleyersburg.de"
                    >
                        <flux:icon.github class="size-4"/>
                        See on GitHub
                    </a>
                    <span class="hidden sm:block">•</span>
                    <a
                        class="inline-flex gap-1 items-center text-slate-600 dark:text-slate-300"
                        href="/feed.xml"
                    >
                        <flux:icon.rss class="size-4"/>
                        RSS Feed
                    </a>
                    <span class="hidden">•</span>
                    <div class="hidden">Code highlighting provided by <a href="https://torchlight.dev">torchlight.dev</a></div>
                </div>
            </footer>
        @endisset
    </div>

    @fluxScripts

    @production
        @if (! str_contains(request()->route()->action['prefix'] ?? '', 'holocron'))
            <script async
                    defer
                    data-website-id="428e7134-523b-4449-aa87-f45e94f5d525"
                    src="https://c3po.wacg.dev/protocol.js"/>
        @endif

        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            window.location.reload()
                            preventDefault()
                        }
                    })
                })
            })
        </script>
    @endproduction
    </body>
@show
</html>
