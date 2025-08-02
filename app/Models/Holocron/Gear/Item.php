<?php

namespace App\Models\Holocron\Gear;

use Database\Factories\Holocron\Gear\ItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $category_id
 * @property string $name
 * @property float $quantity_per_day
 * @property int $quantity
 * @property array|null $properties
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    protected $table = 'gear_items';

    protected $casts = [
        'properties' => 'array',
    ];
}
