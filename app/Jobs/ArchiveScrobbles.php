<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Scrobble;
use App\Services\LastFm;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ArchiveScrobbles implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const int LIMIT = 500;

    public function __construct(public ?int $page = null) {}

    public function handle(LastFm $lastFm): void
    {
        Log::info('[ArchiveScrobbles] Job started', ['page' => $this->page]);

        $localScrobbles = Scrobble::query()->count();

        $latestScrobble = $lastFm->getRecentTracks(1);

        $totalScrobbles = (int) data_get($latestScrobble, '@attr.total');

        Log::info('[ArchiveScrobbles] Count check', [
            'local' => $localScrobbles,
            'remote' => $totalScrobbles,
            'response_keys' => array_keys($latestScrobble ?: []),
        ]);

        if ($totalScrobbles === $localScrobbles) {
            Log::info('[ArchiveScrobbles] Counts match, skipping');

            return;
        }

        $totalPages = (int) ceil($totalScrobbles / self::LIMIT);

        $pageToFetch = max($this->page ?? (int) ceil($totalPages - ($localScrobbles / self::LIMIT)), 1);

        $allScrobbles = collect(data_get($lastFm->getRecentTracks(limit: self::LIMIT, page: $pageToFetch), 'track'));
        $scrobbles = $allScrobbles->reject(fn (mixed $scrobble) => ! data_get($scrobble, 'date.uts'));

        Log::info('[ArchiveScrobbles] Fetched page', [
            'page' => $pageToFetch,
            'total_pages' => $totalPages,
            'tracks_returned' => $allScrobbles->count(),
            'tracks_with_date' => $scrobbles->count(),
        ]);

        $data = $scrobbles->map(function (mixed $scrobble) {
            return [
                'artist' => data_get($scrobble, 'artist.#text'),
                'album' => data_get($scrobble, 'album.#text'),
                'track' => data_get($scrobble, 'name'),
                'played_at' => Carbon::createFromTimestamp(data_get($scrobble, 'date.uts')),
                'payload' => json_encode($scrobble),
            ];
        })->filter();

        Scrobble::query()->upsert($data->toArray(), ['artist', 'track', 'played_at']);

        $newCount = Scrobble::query()->count();
        Log::info('[ArchiveScrobbles] Upserted', [
            'records' => $data->count(),
            'new_total' => $newCount,
            'added' => $newCount - $localScrobbles,
        ]);

        if ($pageToFetch === 1) {
            return;
        }

        $nextPage = $scrobbles->count() ? $pageToFetch - 1 : $pageToFetch;
        Log::info('[ArchiveScrobbles] Dispatching next page', ['next_page' => $nextPage]);

        self::dispatch($nextPage);
    }
}
