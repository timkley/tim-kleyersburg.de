<?php

declare(strict_types=1);

namespace App\Models\Holocron\Quest;

use App\Enums\Holocron\ExperienceType;
use App\Enums\Holocron\QuestStatus;
use App\Models\User;
use App\Models\Webpage;
use Database\Factories\Holocron\Quest\QuestFactory;
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

/** @property Collection $images */
/** @property QuestStatus $status */
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
                $this->update(['accepted' => false]);
                User::tim()->addExperience(2, ExperienceType::QuestCompleted, $this->id);
            });
        }
    }

    public function accept(): void
    {
        $this->update(['accepted' => true]);
    }

    public function unaccept(): void
    {
        $this->update(['accepted' => false]);
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
     * @return Collection<int, Quest>
     */
    public function breadcrumb(): Collection
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
            'created_at' => $this->created_at->timestamp,
        ]);
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
    protected function accepted(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('accepted', true);
    }

    /**
     * @param  EloquentBuilder<Quest>  $query
     * @return EloquentBuilder<Quest>
     */
    #[Scope]
    protected function notAccepted(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('accepted', false);
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
        ];
    }
}
