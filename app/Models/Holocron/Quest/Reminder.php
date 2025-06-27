<?php

declare(strict_types=1);

namespace App\Models\Holocron\Quest;

use Carbon\CarbonImmutable;
use Cron\CronExpression;
use Database\Factories\Holocron\Quest\ReminderFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

/**
 * @property-read Quest $quest
 */
class Reminder extends Model
{
    /** @use HasFactory<ReminderFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'quest_reminders';

    protected $casts = [
        'remind_at' => 'datetime',
        'last_processed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Quest, $this>
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    public function markAsProcessed(): void
    {
        $this->update([
            'last_processed_at' => now(),
        ]);

        // If this is a recurring reminder (has a recurrence pattern), schedule the next occurrence
        if (! empty($this->recurrence_pattern)) {
            $this->scheduleNextOccurrence();
        }
    }

    /**
     * @param  EloquentBuilder<Reminder>  $query
     * @return EloquentBuilder<Reminder>
     */
    #[Scope]
    protected function due(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('remind_at', '<=', now())
            ->where(function (EloquentBuilder $query) {
                $query->whereNull('last_processed_at')
                    ->orWhereColumn('last_processed_at', '<', 'remind_at');
            });
    }

    protected function scheduleNextOccurrence(): void
    {
        if (empty($this->recurrence_pattern)) {
            throw new InvalidArgumentException('Cannot calculate next occurrence without a recurrence pattern');
        }

        $cron = new CronExpression($this->recurrence_pattern);

        $nextRemindAt = CarbonImmutable::parse($cron->getNextRunDate(now()));

        $this->update([
            'remind_at' => $nextRemindAt,
            'last_processed_at' => null,
        ]);
    }
}
