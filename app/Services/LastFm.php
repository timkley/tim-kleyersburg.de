<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class LastFm
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $username,
    ) {}

    /**
     * @return array<int, mixed>
     */
    public function getRecentTracks(int $limit = 100, ?int $page = null): array
    {
        $response = Http::get('https://ws.audioscrobbler.com/2.0/', [
            'method' => 'user.getrecenttracks',
            'user' => $this->username,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => $limit,
            'page' => $page,
        ]);

        return $response->json('recenttracks') ?? [];
    }

    /**
     * @return array<int, array{name:string,url:string}>
     *
     * @throws ConnectionException
     */
    public function getTopArtists(int $limit = 1, string $period = '3month'): array
    {
        $response = Http::get('https://ws.audioscrobbler.com/2.0/', [
            'method' => 'user.gettopartists',
            'user' => $this->username,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => $limit,
            'period' => $period,
        ]);

        return $response->json('topartists.artist', []);
    }
}
