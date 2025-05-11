<?php

declare(strict_types=1);

namespace App\Models\Holocron;

use Database\Factories\Holocron\QuestNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestNote extends Model
{
    /** @use HasFactory<QuestNoteFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Quest, $this>
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }
}
