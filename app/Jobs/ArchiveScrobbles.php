<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Scrobble;
use App\Services\LastFm;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchiveScrobbles implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const int LIMIT = 500;

    public function __construct(public ?int $page = null) {}

    public function handle(LastFm $lastFm): void
    {
        $localScrobbles = Scrobble::count();

        $latestScrobble = $lastFm->getRecentTracks(1);

        $totalScrobbles = (int) data_get($latestScrobble, '@attr.total');

        if ($totalScrobbles === $localScrobbles) {
            return;
        }

        $totalPages = (int) ceil($totalScrobbles / self::LIMIT);

        $pageToFetch = $this->page ?? (int) ceil($totalPages - ($localScrobbles / self::LIMIT));

        $scrobbles = collect(data_get($lastFm->getRecentTracks(limit: self::LIMIT, page: $pageToFetch), 'track'))
            ->reject(fn ($scrobble) => ! data_get($scrobble, 'date.uts'));

        $data = $scrobbles->map(function ($scrobble) {
            if (! data_get($scrobble, 'date.uts')) {
                return null;
            }

            return [
                'artist' => data_get($scrobble, 'artist.#text'),
                'album' => data_get($scrobble, 'album.#text'),
                'track' => data_get($scrobble, 'name'),
                'played_at' => Carbon::createFromTimestamp(data_get($scrobble, 'date.uts')),
                'payload' => json_encode($scrobble),
            ];
        })->filter();

        Scrobble::query()->upsert($data->toArray(), ['artist', 'track', 'played_at']);

        if ($scrobbles->count() <= self::LIMIT) {
//            self::dispatch(max($pageToFetch - 1, 1));
        }
    }
}
