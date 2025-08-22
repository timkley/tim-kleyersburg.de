<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Holocron\Gear\Database\Factories\JourneyItemFactory;

/**
 * @property int $id
 * @property int $journey_id
 * @property int $item_id
 * @property int $quantity
 * @property bool $packed_for_departure
 * @property bool $packed_for_return
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class JourneyItem extends Model
{
    /** @use HasFactory<JourneyItemFactory> */
    use HasFactory;

    protected $table = 'gear_journey_items';

    protected $casts = [
        'packed_for_departure' => 'boolean',
        'packed_for_return' => 'boolean',
    ];

    /**
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    protected static function newFactory(): JourneyItemFactory
    {
        return JourneyItemFactory::new();
    }
}
