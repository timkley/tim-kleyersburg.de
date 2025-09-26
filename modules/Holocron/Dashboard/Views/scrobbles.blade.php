@use(Illuminate\Support\Number)

<div class="space-y-4">
    <flux:heading size="xl">
        {{ Number::format(number: $count, locale: 'de-DE') }} Scrobbles
    </flux:heading>

    <div class="space-y-1">
        @foreach($scrobbles as $scrobble)
            <div class="flex justify-between">
                <div>
                    {{ $scrobble->artist }} - {{ $scrobble->track }}
                </div>
                <div class="font-mono">
                    {{ $scrobble->played_at->shiftTimezone('UTC')->setTimezone(config('app.timezone'))->format('d.m.Y H:i') }}
                </div>
            </div>
        @endforeach
    </div>

    <flux:pagination :paginator="$scrobbles" />
</div>
