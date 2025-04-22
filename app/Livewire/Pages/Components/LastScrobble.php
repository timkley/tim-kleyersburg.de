<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Components;

use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class LastScrobble extends Component
{
    public function render(): View
    {
        $track = cache()->remember('lastfm:last-scrobble', now()->addMinutes(5), function () {
            $response = Http::withQueryParameters([
                'api_key' => config('services.lastfm.api_key'),
                'format' => 'json',
                'method' => 'user.getrecenttracks',
                'user' => 'timmotheus',
                'limit' => 1,
            ])->get('https://ws.audioscrobbler.com/2.0');

            return data_get($response->json(), 'recenttracks.track.0');
        });

        $topArtist = cache()->remember('lastfm:weekly-artist', now()->addWeek(), function () {
            $response = Http::withQueryParameters([
                'api_key' => config('services.lastfm.api_key'),
                'format' => 'json',
                'method' => 'user.gettopartists',
                'user' => 'timmotheus',
                'limit' => 1,
                'period' => '3month',
            ])->get('https://ws.audioscrobbler.com/2.0');

            return data_get($response->json(), 'topartists.artist.0');
        });

        return view('pages.components.last-scrobble', compact('track', 'topArtist'));
    }

    public function placeholder()
    {
        return view('pages.components.last-scrobble');
    }
}
