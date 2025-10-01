<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Model;

class HealthData extends Model
{
    protected $table = 'grind_health_data';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'original_payload' => 'array',
            'qty' => 'decimal:2',
        ];
    }
}
