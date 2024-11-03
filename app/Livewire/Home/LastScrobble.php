<?php

namespace App\Livewire\Home;

use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class LastScrobble extends Component
{
    public function render()
    {
        $response = Http::withQueryParameters([
            'api_key' => config('services.lastfm.api_key'),
            'format' => 'json',
            'method' => 'user.getrecenttracks',
            'user' => 'timmotheus',
            'limit' => 1,
        ])->get('http://ws.audioscrobbler.com/2.0');

        return view('livewire.home.last-scrobble', [
            'track' => data_get($response->json(), 'recenttracks.track.0'),
        ]);
    }

    public function placeholder()
    {
        return view('livewire.home.last-scrobble');
    }
}
