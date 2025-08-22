<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Holocron\User\Database\Factories\ExperienceLogFactory;
use Modules\Holocron\User\Enums\ExperienceType;

/**
 * @property int $amount
 * @property int $identifier
 * @property ExperienceType $type
 */
class ExperienceLog extends Model
{
    /** @use HasFactory<ExperienceLogFactory> */
    use HasFactory;

    protected static function newFactory(): ExperienceLogFactory
    {
        return ExperienceLogFactory::new();
    }

    protected function casts(): array
    {
        return [
            'type' => ExperienceType::class,
        ];
    }
}
