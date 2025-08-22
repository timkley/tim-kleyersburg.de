<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Holocron\Quest\Database\Factories\NoteFactory;

/**
 * @property-read ?string $content
 * @property-read string $role
 */
class Note extends Model
{
    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'quest_notes';

    /**
     * @return BelongsTo<Quest, $this>
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }
}
