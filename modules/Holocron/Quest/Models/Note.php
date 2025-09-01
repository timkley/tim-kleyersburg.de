<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use Modules\Holocron\Quest\Database\Factories\NoteFactory;

/**
 * @property-read ?string $content
 * @property-read string $role
 */
class Note extends Model
{
    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    use Searchable;

    /** @var string */
    protected $table = 'quest_notes';

    /**
     * @return BelongsTo<Quest, $this>
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }
    /**
     * @return string[]
     */
    public function toSearchableArray(): array
    {
        return array_merge($this->toArray(), [
            'id' => (string) $this->id,
            'quest_id' => (string) $this->quest_id,
            'content' => $this->content,
            'created_at' => $this->created_at->timestamp,
        ]);
    }

    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }
}
