<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Holocron\Quest\Database\Factories\QuestRecurrenceFactory;
use Modules\Holocron\Quest\Enums\QuestRecurrenceType;

/**
 * @property-read Quest $quest
 * @property-read QuestRecurrenceType $type
 * @property-read ?Carbon $last_recurred_at
 * @property-read ?Carbon $ends_at
 */
class QuestRecurrence extends Model
{
    use HasFactory;

    protected $table = 'quest_recurrences';

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    protected static function newFactory(): QuestRecurrenceFactory
    {
        return QuestRecurrenceFactory::new();
    }

    protected function casts(): array
    {
        return [
            'type' => QuestRecurrenceType::class,
            'last_recurred_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
