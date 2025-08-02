<?php

namespace App\Models\Holocron\Gear;

use Database\Factories\Holocron\Gear\JourneyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $destination
 * @property float $latitude
 * @property float $longitude
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property array $participants
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Journey extends Model
{
    /** @use HasFactory<JourneyFactory> */
    use HasFactory;

    protected $table = 'gear_journeys';

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'participants' => 'array',
    ];
}
