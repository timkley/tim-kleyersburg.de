<div class="mx-auto h-auto w-64 rounded bg-sky-100 p-2 shadow-md transition hover:bg-sky-100/80 sm:w-60 dark:bg-slate-700 dark:hover:bg-slate-700/80">
    <div class="mb-5 text-center">
        <span class="text-sm text-gray-700 dark:text-gray-300"> Last Scrobble </span>
        <a href="https://www.last.fm/user/Timmotheus">@timmotheus</a>
    </div>
    <div class="text-center">
        @if (isset($track))
            <img
                width="64"
                height="64"
                class="mx-auto mb-2 rounded shadow"
                src="{{ $track['image'][1]['#text'] }}"
                srcset="{{ $track['image'][1]['#text'] }} 1x, {{ $track['image'][2]['#text'] }} 2x"
                alt="{{ $track['artist']['#text'] }} {{ $track['name'] }}"
            />
            <div class="truncate font-bold">{{ $track['name'] }}</div>
            <div class="truncate text-sm">{{ $track['artist']['#text'] }}</div>

            <hr class="opacity-25 my-4 mx-8">

            <div class="mb-2 truncate text-xs opacity-50">Top artist last 3 months</div>
            <div class="font-bold">
                {{ $topArtist['name'] }} <span class="font-normal text-sm">{{ Number::format($topArtist['playcount']) }} plays</span>
            </div>
        @else
            <div class="animate-pulse text-center">
                <div class="mx-auto mb-2 h-16 w-16 rounded bg-sky-50 dark:bg-slate-800"></div>
                <div class="mb-2 h-5 rounded-full bg-sky-50 dark:bg-slate-800"></div>
                <div class="h-4 rounded-full bg-sky-50 dark:bg-slate-800"></div>

                <hr class="opacity-25 my-4">

                <div class="mb-2 h-4 rounded-full bg-sky-50 dark:bg-slate-800"></div>
                <div class="h-6 rounded-full bg-sky-50 dark:bg-slate-800"></div>
            </div>
        @endif
    </div>
</div>
