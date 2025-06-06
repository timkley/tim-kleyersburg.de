<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ExperienceLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $amount
 * @property int $identifier
 * @property string $type
 */
class ExperienceLog extends Model
{
    /** @use HasFactory<ExperienceLogFactory> */
    use HasFactory;
}
