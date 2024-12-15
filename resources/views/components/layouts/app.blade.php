<!DOCTYPE html>
<html
    lang="de"
    class="scroll-smooth"
>
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"
    />
    @if (isset($seo))
        {!! $seo !!}
    @else
        <meta
            name="description"
            content="{{ $metaDescription ?? '' }}"
        />
        <title>{{ $title ?? config('app.name') }}</title>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxStyles

    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    />
    <link
        href="https://fonts.bunny.net/css?family=ibm-plex-sans:600|source-sans-pro:400,600"
        rel="stylesheet"
    />

    <link
        rel="apple-touch-icon"
        sizes="180x180"
        href="/apple-touch-icon.png"
    />
    <link
        rel="icon"
        type="image/png"
        sizes="32x32"
        href="/favicon-32x32.png"
    />
    <link
        rel="icon"
        type="image/png"
        sizes="16x16"
        href="/favicon-16x16.png"
    />
    <link
        rel="manifest"
        href="/site.webmanifest"
    />
</head>
<body class="flex flex-col p-2 sm:p-3 md:p-4 bg-linear-to-tr from-blue-700 to-blue-300 dark:from-blue-700 dark:to-blue-900 min-h-svh font-sans {{ $additionalBodyClasses ?? '' }}">
    <div class="h-lvh flex-1 rounded-sm bg-sky-50 px-6 py-4 text-slate-900 selection:bg-blue-200 sm:rounded-md md:rounded-lg dark:bg-slate-800 dark:text-slate-300">
        @isset($header)
            {{ $header }}
        @else
            <header class="mx-auto mb-8 flex max-w-3xl items-center justify-between">
                <x-logo />

                <div class="space-x-2">
                    <a
                        href="{{ route('articles.index') }}"
                        wire:navigate
                        >Articles</a
                    >
                </div>
            </header>
        @endisset

        {{ $slot }}

        @isset($footer)
            {{ $footer }}
        @else
            <footer class="mx-auto mt-12 max-w-3xl">
                <div class="flex flex-col items-start gap-3 text-sm sm:flex-row sm:space-y-0">
                    <a
                        class="inline-flex items-center text-slate-600 dark:text-slate-300"
                        href="https://github.com/timkley/tim-kleyersburg.de"
                    >
                        <div class="mr-1 h-4 w-4 shrink-0">
                            <svg
                                class="fill-current"
                                role="img"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <title>GitHub</title><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" />
                            </svg>
                        </div>
                        See on GitHub
                    </a>
                    <span class="hidden sm:block">•</span>
                    <a
                        class="inline-flex items-center text-slate-600 dark:text-slate-300"
                        href="/feed.xml"
                    >
                        RSS Feed
                    </a>
                    <span class="hidden sm:block">•</span>
                    <div>Code highlighting provided by <a href="https://torchlight.dev">torchlight.dev</a></div>
                </div>
            </footer>
        @endisset
    </div>

    @livewireScripts
    @fluxScripts
    {{ $scripts ?? '' }}

    @production
        <script
            async
            defer
            data-website-id="428e7134-523b-4449-aa87-f45e94f5d525"
            src="https://c3po.wacg.dev/protocol.js"
        ></script>
    @endproduction
</body>
</html>
