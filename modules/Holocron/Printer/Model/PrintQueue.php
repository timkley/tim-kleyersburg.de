<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Holocron\Printer\Database\Factories\PrintQueueFactory;

/**
 * @property int $id
 * @property string $image
 * @property array $actions
 * @property Carbon|null $printed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PrintQueue extends Model
{
    /** @use HasFactory<PrintQueueFactory> */
    use HasFactory;

    protected static function newFactory(): PrintQueueFactory
    {
        return PrintQueueFactory::new();
    }

    protected function casts(): array
    {
        return [
            'actions' => 'array',
            'printed_at' => 'datetime',
        ];
    }
}
