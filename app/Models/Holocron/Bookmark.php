<?php

declare(strict_types=1);

namespace App\Models\Holocron;

use App\Models\Webpage;
use Database\Factories\Holocron\BookmarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Bookmark extends Model
{
    /** @use HasFactory<BookmarkFactory> */
    use HasFactory;

    use Searchable;

    /**
     * @return BelongsTo<Webpage, $this>
     */
    public function webpage(): BelongsTo
    {
        return $this->belongsTo(Webpage::class);
    }

    /**
     * @return array<string,mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'url' => $this->webpage->url,
            'title' => $this->webpage->title,
            'description' => $this->webpage->description,
            'summary' => $this->webpage->summary,
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
