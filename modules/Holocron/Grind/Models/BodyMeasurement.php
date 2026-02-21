<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Holocron\Grind\Database\Factories\BodyMeasurementFactory;

/**
 * @property-read \Carbon\CarbonImmutable $date
 * @property-read float $weight
 * @property-read ?float $body_fat
 * @property-read ?float $muscle_mass
 * @property-read ?int $visceral_fat
 * @property-read ?float $bmi
 * @property-read ?float $body_water
 */
class BodyMeasurement extends Model
{
    /** @use HasFactory<BodyMeasurementFactory> */
    use HasFactory;

    protected $table = 'grind_body_measurements';

    protected static function newFactory(): BodyMeasurementFactory
    {
        return BodyMeasurementFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight' => 'decimal:2',
            'body_fat' => 'decimal:1',
            'muscle_mass' => 'decimal:1',
            'bmi' => 'decimal:1',
            'body_water' => 'decimal:1',
        ];
    }
}
