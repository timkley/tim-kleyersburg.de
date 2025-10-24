<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Holocron\Quest\Database\Factories\QuestRecurrenceFactory;

/**
 * @property-read Quest $quest
 * @property-read int $every_x_days
 * @property-read string $recurrence_type
 * @property-read ?Carbon $last_recurred_at
 * @property-read ?Carbon $ends_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class QuestRecurrence extends Model
{
    use HasFactory;

    public const TYPE_RECURRENCE_BASED = 'recurrence_based';

    public const TYPE_COMPLETION_BASED = 'completion_based';

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
            'last_recurred_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
