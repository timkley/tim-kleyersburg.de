<?php

declare(strict_types=1);

namespace App\Models\Holocron;

use App\Enums\Holocron\QuestStatus;
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
            ->when(! $this->exists, fn ($query) => $query->orWhereNull('quest_id'));
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

    protected function casts(): array
    {
        return [
            'status' => QuestStatus::class,
        ];
    }
}
