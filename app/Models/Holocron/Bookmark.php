<?php

declare(strict_types=1);

namespace App\Models\Holocron;

use App\Models\Webpage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    /** @use HasFactory<\Database\Factories\Holocron\BookmarkFactory> */
    use HasFactory;

    public function webpage(): BelongsTo
    {
        return $this->belongsTo(Webpage::class);
    }
}
