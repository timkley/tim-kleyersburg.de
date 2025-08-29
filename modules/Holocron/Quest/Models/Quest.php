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
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Searchable;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Database\Factories\QuestFactory;
use Modules\Holocron\Quest\Enums\QuestStatus;
use Modules\Holocron\User\Enums\ExperienceType;
use Modules\Holocron\User\Models\User;

/**
 * @property-read ?int $quest_id
 * @property-read ?CarbonImmutable $date
 * @property-read string $name
 * @property-read string $description
 * @property-read \Illuminate\Support\Collection<int,string> $images
 * @property-read QuestStatus $status
 * @property-read bool $accepted
 * @property-read bool $daily
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 */
class Quest extends Model
{
    /** @use HasFactory<QuestFactory> */
    use HasFactory;

    use Searchable;

    public function setStatus(QuestStatus $status): void
    {
        $this->update(['status' => $status]);

        if ($status === QuestStatus::Complete) {
            defer(function () {
                User::tim()->addExperience(2, ExperienceType::QuestCompleted, $this->id);
            });
        }
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
        return $this->belongsToMany(Webpage::class)->withPivot('title');
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
     * @return \Illuminate\Support\Collection<int, Quest>
     */
    public function breadcrumb(): \Illuminate\Support\Collection
    {
        $breadcrumb = new Collection;
        $current = $this;

        if ($current->exists) {
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
        return $query->whereNot('status', QuestStatus::Complete);
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
                ->whereNot('children.status', QuestStatus::Complete);
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
        return $query->whereNotNull('date')->where('daily', false);
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
    protected function dailyAgenda(EloquentBuilder $query, CarbonImmutable $date): EloquentBuilder
    {
        return $query->whereHas('parent', function (EloquentBuilder $query) {
            $query->whereNotNull('date');
        })
            ->where(function (EloquentBuilder $query) use ($date) {
                $query->where(function (EloquentBuilder $query) use ($date) {
                    $query->whereHas('parent', function (EloquentBuilder $query) use ($date) {
                        $query->whereDate('date', '<', $date);
                    })->notCompleted();
                })->orWhereHas('parent', function (EloquentBuilder $query) use ($date) {
                    $query->whereDate('date', $date);
                });
            });
    }

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'status' => QuestStatus::class,
            'images' => AsCollection::class,
            'accepted' => 'boolean',
            'date' => 'date:Y-m-d',
        ];
    }
}
