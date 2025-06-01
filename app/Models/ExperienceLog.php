<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ExperienceLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $amount
 * @property-read string $type
 * @property-read string $description
 */
class ExperienceLog extends Model
{
    /** @use HasFactory<ExperienceLogFactory> */
    use HasFactory;
}
