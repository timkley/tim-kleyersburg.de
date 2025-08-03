<?php

declare(strict_types=1);

namespace App\Models\Holocron\Gear;

use Database\Factories\Holocron\Gear\JourneyItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
}
