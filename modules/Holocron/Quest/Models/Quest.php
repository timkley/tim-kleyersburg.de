<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Searchable;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Printer\Services\Printer;
use Modules\Holocron\Quest\Database\Factories\QuestFactory;
use Modules\Holocron\User\Enums\ExperienceType;
use Modules\Holocron\User\Models\User;

/**
 * @property-read ?int $quest_id
 * @property-read ?CarbonImmutable $date
 * @property-read string $name
 * @property-read string $description
 * @property-read \Illuminate\Support\Collection<int,string> $images
 * @property-read bool $is_note
 * @property-read ?CarbonImmutable $completed_at
 * @property-read bool $accepted
 * @property-read bool $daily
 * @property-read bool $should_be_printed
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 */
class Quest extends Model
{
    /** @use HasFactory<QuestFactory> */
    use HasFactory;

    use Searchable;

    public function complete(): void
    {
        $this->update(['completed_at' => now()]);

        defer(function () {
            User::tim()->addExperience(2, ExperienceType::QuestCompleted, $this->id);
        });
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * @return BelongsTo<Quest, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'quest_id');
    }

    /**
     * @return HasMany<Quest, $this>
     */
    public function children(): HasMany
    {
        /** @var HasMany<Quest, $this> */
        return $this->hasMany(self::class, 'quest_id');
    }

    /**
     * @return BelongsToMany<Webpage, $this>
     */
    public function webpages(): BelongsToMany
    {
        return $this->belongsToMany(Webpage::class)->withPivot('id', 'title');
    }

    /**
     * @return HasMany<Note, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * @return HasMany<Reminder, $this>
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * @return HasOne<QuestRecurrence, $this>
     */
    public function recurrence(): HasOne
    {
        return $this->hasOne(QuestRecurrence::class);
    }

    /**
     * @return BelongsTo<QuestRecurrence, $this>
     */
    public function instanceOf(): BelongsTo
    {
        return $this->belongsTo(QuestRecurrence::class, 'created_from_recurrence_id');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Quest>
     */
    public function breadcrumb(bool $withCurrent = false): \Illuminate\Support\Collection
    {
        $breadcrumb = new Collection;
        $current = $this;

        if ($withCurrent && $current->exists) {
            $breadcrumb->push($current);
        }

        while ($current->quest_id !== null) {
            $current = self::find($current->quest_id);
            if ($current === null) {
                break;
            }
            $breadcrumb->push($current);
        }

        return $breadcrumb->reverse()->values();
    }

    /**
     * @return string[]
     */
    public function toSearchableArray(): array
    {
        return array_merge($this->toArray(), [
            'id' => (string) $this->id,
            'date' => $this->date?->timestamp,
            'completed_at' => $this->completed_at->timestamp ?? 0,
            'breadcrumb' => $this->breadcrumb()->pluck('name')->join(' > '),
            'created_at' => $this->created_at->timestamp,
        ]);
    }

    protected static function newFactory(): QuestFactory
    {
        return QuestFactory::new();
    }

    /**
     * @param  EloquentBuilder<Quest>  $query
     * @return EloquentBuilder<Quest>
     */
    #[Scope]
    protected function notCompleted(EloquentBuilder $query): EloquentBuilder
    {
        return $query->whereNull('completed_at');
    }

    /**
     * @param  EloquentBuilder<Quest>  $query
     * @return EloquentBuilder<Quest>
     */
    #[Scope]
    protected function completed(EloquentBuilder $query): EloquentBuilder
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * @param  EloquentBuilder<Quest>  $query
     * @return EloquentBuilder<Quest>
     */
    #[Scope]
    protected function noChildren(EloquentBuilder $query): EloquentBuilder
    {
        return $query->whereNotExists(function (QueryBuilder $query): void {
            $query->from('quests as children')
                ->whereColumn('children.quest_id', 'quests.id')
                ->whereNull('children.completed_at');
        });
    }

    /**
     * @param  EloquentBuilder<Quest>  $query
     * @return EloquentBuilder<Quest>
     */
    #[Scope]
    protected function notDaily(EloquentBuilder $query): EloquentBuilder
    {
        return $query->whereNull('date');
    }

    /**
     * @param  EloquentBuilder<Quest>  $query
     * @return EloquentBuilder<Quest>
     */
    #[Scope]
    protected function today(EloquentBuilder $query): EloquentBuilder
    {
        return $query->whereDate('date', '<=', today())->where('daily', false);
    }

    /**
     * @param  EloquentBuilder<Quest>  $query
     * @return EloquentBuilder<Quest>
     */
    #[Scope]
    protected function notToday(EloquentBuilder $query): EloquentBuilder
    {
        return $query->whereNull('date')->where('daily', false);
    }

    /**
     * @param  EloquentBuilder<Quest>  $query
     * @return EloquentBuilder<Quest>
     */
    #[Scope]
    protected function areNotNotes(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('is_note', false);
    }

    /**
     * @param  EloquentBuilder<Quest>  $query
     * @return EloquentBuilder<Quest>
     */
    #[Scope]
    protected function areNotes(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('is_note', true);
    }

    protected static function booted(): void
    {
        static::saved(function (Quest $quest) {
            $shouldPrint = (($quest->wasRecentlyCreated && $quest->should_be_printed) ||
                           $quest->wasChanged('should_be_printed')) &&
                           $quest->should_be_printed;

            if ($shouldPrint) {
                Printer::print('holocron-quest::print-view', ['quest' => $quest], [route('holocron.quests.complete', [$quest])]);
            }
        });
    }

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'daily' => 'boolean',
            'images' => AsCollection::class,
            'completed_at' => 'datetime',
            'is_note' => 'boolean',
        ];
    }
}
