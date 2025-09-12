<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $artist
 * @property string $track
 * @property string $album
 * @property Carbon $played_at
 * @property array<string, mixed> $payload
 */
class Scrobble extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
            'payload' => 'array',
        ];
    }
}
