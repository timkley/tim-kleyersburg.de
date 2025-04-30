<?php

declare(strict_types=1);

namespace App\Models\Holocron;

use App\Enums\Holocron\QuestStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    /** @use HasFactory<\Database\Factories\Holocron\QuestFactory> */
    use HasFactory;

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'quest_id')
            ->when(! $this->exists, fn ($query) => $query->orWhereNull('quest_id'))
            ->notCompleted();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(QuestNote::class);
    }

    public function getBreadcrumb(): Collection
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

        return $breadcrumb->reverse();
    }

    #[Scope]
    protected function notCompleted(EloquentBuilder $query)
    {
        return $query->whereNot('status', QuestStatus::Complete);
    }

    #[Scope]
    protected function leafNodes(EloquentBuilder $query): EloquentBuilder
    {
        return $query->whereNot('status', QuestStatus::Complete)
            ->whereNotExists(function (Builder $query) {
                $query->from('quests as children')
                    ->whereColumn('children.quest_id', 'quests.id')
                    ->whereNot('children.status', QuestStatus::Complete);
            });
    }

    protected function casts(): array
    {
        return [
            'status' => QuestStatus::class,
            'images' => AsCollection::class,
        ];
    }
}
