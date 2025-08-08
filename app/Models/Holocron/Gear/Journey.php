<?php

declare(strict_types=1);

namespace App\Models\Holocron\Gear;

use App\Data\Forecast;
use App\Services\Weather;
use Carbon\CarbonImmutable;
use Database\Factories\Holocron\Gear\JourneyFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $destination
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property array|string[] $participants
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

    /**
     * @return HasMany<JourneyItem, $this>
     */
    public function journeyItems(): HasMany
    {
        return $this->hasMany(JourneyItem::class);
    }

    public function forecast(): Forecast
    {
        return Weather::forecast($this->destination, $this->starts_at, $this->ends_at);
    }

    /**
     * @return Attribute<int, never>
     */
    protected function days(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->starts_at->diffInDays($this->ends_at) + 1
        );
    }
}
