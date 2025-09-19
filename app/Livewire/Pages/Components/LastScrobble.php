<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Components;

use App\Services\LastFm;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class LastScrobble extends Component
{
    public function render(LastFm $lastfm): View
    {
        $track = cache()->remember('lastfm:last-scrobble', now()->addMinutes(5), function () use ($lastfm) {
            return data_get($lastfm->getRecentTracks(), 'track.0');
        });

        $topArtist = cache()->remember('lastfm:weekly-artist', now()->addWeek(), function () use ($lastfm) {
            return $lastfm->getTopArtists()[0];
        });

        return view('pages.components.last-scrobble', ['track' => $track, 'topArtist' => $topArtist]);
    }

    public function placeholder(): View
    {
        return view('pages.components.last-scrobble');
    }
}
