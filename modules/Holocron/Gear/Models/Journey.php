<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Models;

use App\Data\Forecast;
use App\Services\Weather;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Holocron\Gear\Database\Factories\JourneyFactory;
use Modules\Holocron\Gear\Enums\Property;

/**
 * @property int $id
 * @property string $destination
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property ?Collection<int,Property> $properties
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Journey extends Model
{
    /** @use HasFactory<JourneyFactory> */
    use HasFactory;

    protected $table = 'gear_journeys';

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

    protected static function newFactory(): JourneyFactory
    {
        return JourneyFactory::new();
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

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'properties' => AsEnumCollection::of(Property::class),
        ];
    }
}
