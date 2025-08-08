<?php

declare(strict_types=1);

namespace App\Models\Holocron\Gear;

use App\Enums\Holocron\Gear\Property;
use Database\Factories\Holocron\Gear\ItemFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int|null $category_id
 * @property string $name
 * @property float $quantity_per_day
 * @property int $quantity
 * @property ?Collection<int,Property> $properties
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    protected $table = 'gear_items';

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'properties' => AsEnumCollection::of(Property::class),
        ];
    }
}
