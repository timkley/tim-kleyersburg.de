<?php

declare(strict_types=1);

namespace App\Models\Holocron\Health;

use App\Enums\Holocron\Health\IntakeTypes;
use App\Enums\Holocron\Health\IntakeUnits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intake extends Model
{
    /** @use HasFactory<\Database\Factories\Holocron\Health\IntakeFactory> */
    use HasFactory;

    protected $casts = [
        'type' => IntakeTypes::class,
        'unit' => IntakeUnits::class,
    ];
}
