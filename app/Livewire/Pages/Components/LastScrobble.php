<?php

namespace App\Livewire\Pages\Components;

use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class LastScrobble extends Component
{
    public function render()
    {
        $track = cache()->remember('last-scrobble', now()->addMinutes(5), function () {
            $response = Http::withQueryParameters([
                'api_key' => config('services.lastfm.api_key'),
                'format' => 'json',
                'method' => 'user.getrecenttracks',
                'user' => 'timmotheus',
                'limit' => 1,
            ])->get('http://ws.audioscrobbler.com/2.0');

            return data_get($response->json(), 'recenttracks.track.0');
        });

        return view('pages.components.last-scrobble', compact('track'));
    }

    public function placeholder()
    {
        return view('pages.components.last-scrobble');
    }
}
