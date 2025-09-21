<div>
    <div class="font-medium mb-4">{{ $count }} Scrobbles</div>

    @foreach($scrobbles as $scrobble)
        <p>
            {{ $scrobble->artist }} -
            {{ $scrobble->track }}
            {{ $scrobble->played_at }}
        </p>
    @endforeach
</div>
