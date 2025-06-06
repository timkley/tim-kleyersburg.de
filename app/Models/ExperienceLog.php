<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Holocron\ExperienceType;
use Database\Factories\ExperienceLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $amount
 * @property int $identifier
 * @property ExperienceType $type
 */
class ExperienceLog extends Model
{
    /** @use HasFactory<ExperienceLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => ExperienceType::class,
        ];
    }
}
