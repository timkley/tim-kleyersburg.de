<?php

declare(strict_types=1);

namespace App\Models\Holocron\Health;

use App\Enums\Holocron\Health\IntakeTypes;
use App\Enums\Holocron\Health\IntakeUnits;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyGoal extends Model
{
    /** @use HasFactory<\Database\Factories\Holocron\Health\DailyGoalFactory> */
    use HasFactory;

    protected $casts = [
        'type' => IntakeTypes::class,
        'unit' => IntakeUnits::class,
        'date' => 'date',
    ];

    public static function for(IntakeTypes $type)
    {
        return self::firstOrCreate(
            [
                'date' => today(),
                'type' => $type,
            ],
            [
                'unit' => $type->unit(),
                'goal' => $type->goal(),
            ]
        );
    }

    protected function reached(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount >= $this->goal,
        );
    }
}
